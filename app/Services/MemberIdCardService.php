<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MemberIdCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Digital member ID cards. Cards show business-safe data only (photo, name,
 * member ID, role, statuses) — never salary, bank, ID numbers or secrets.
 * QR carries a random token; verification resolves it server-side and shows
 * a minimal public page. Revoked cards verify as REVOKED, never valid.
 */
class MemberIdCardService
{
    /** Issue a card; any prior active card becomes replaced (history kept). */
    public function issue(User $user, ?User $issuer = null): array
    {
        return DB::transaction(function () use ($user, $issuer) {
            MemberIdCard::where('user_id', $user->id)->where('status', 'active')
                ->lockForUpdate()->get()
                ->each(fn ($old) => $old->update(['status' => 'replaced', 'revoked_at' => now(), 'revoked_by' => $issuer?->id, 'revoke_reason' => 'Replaced by new card']));

            $card = MemberIdCard::create([
                'card_number' => 'IDC-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'token_hash' => hash('sha256', Str::random(40)),
                'status' => $user->isIdentityVerified() ? 'active' : 'pending',
                'issued_at' => now(),
                'issued_by' => $issuer?->id,
            ]);
            $card = $card->fresh();
            $card->update(['token_hash' => hash('sha256', $this->tokenFor($card))]);
            AuditLog::log('idcard.issued', 'identity', $card, "ID card {$card->card_number} issued for {$user->name} ({$user->member_number}).");
            \App\Services\ServiceTrackingService::notify($user->id, 'idcard_issued', 'Digital ID card issued', "Your member ID card {$card->card_number} is ready.");
            return ['card' => $card->fresh(), 'token' => $this->tokenFor($card)];
        });
    }

    public function revoke(MemberIdCard $card, User $actor, string $reason): MemberIdCard
    {
        abort_unless($actor->isAdmin() || (int) $actor->id === (int) $card->user_id, 403, 'Only the card owner or an administrator can revoke this card.');
        abort_if(trim($reason) === '', 422, 'A revocation reason is required.');
        return DB::transaction(function () use ($card, $actor, $reason) {
            $card = MemberIdCard::lockForUpdate()->findOrFail($card->id);
            abort_unless(in_array($card->status, ['active', 'pending', 'suspended'], true), 422, 'Card cannot be revoked from status ' . $card->status . '.');
            $card->update(['status' => 'revoked', 'revoked_at' => now(), 'revoked_by' => $actor->id, 'revoke_reason' => $reason]);
            AuditLog::log('idcard.revoked', 'identity', $card, "ID card {$card->card_number} revoked by {$actor->name}: {$reason}");
            return $card->fresh();
        });
    }

    public function suspend(MemberIdCard $card, User $actor, string $reason): MemberIdCard
    {
        abort_unless($actor->isAdmin(), 403);
        return DB::transaction(function () use ($card, $actor, $reason) {
            $card = MemberIdCard::lockForUpdate()->findOrFail($card->id);
            abort_unless($card->status === 'active', 422, 'Only active cards can be suspended.');
            $card->update(['status' => 'suspended', 'revoke_reason' => $reason]);
            AuditLog::log('idcard.suspended', 'identity', $card, "ID card {$card->card_number} suspended by {$actor->name}: {$reason}");
            return $card->fresh();
        });
    }

    /**
     * Signed verification token: "<card_id>.<HMAC>". Recomputable
     * server-side (QR re-renderable on every view/PDF), unguessable
     * without APP_KEY, and worthless once the card row leaves active.
     * Sequential IDs are never used raw — the HMAC is mandatory.
     */
    public function tokenFor(MemberIdCard $card): string
    {
        $sig = hash_hmac('sha256', (string) $card->id, config('app.key'));
        return $card->id . '.' . $sig;
    }

    /** Public QR resolution: minimal data only, revoked never valid. */
    public function verifyToken(string $token): array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) return ['valid' => false, 'reason' => 'unknown'];
        $expected = hash_hmac('sha256', $parts[0], config('app.key'));
        if (!hash_equals($expected, $parts[1])) return ['valid' => false, 'reason' => 'unknown'];
        $card = MemberIdCard::with('user')->find((int) $parts[0]);
        if (!$card) return ['valid' => false, 'reason' => 'unknown'];
        $user = $card->user;
        if (in_array($card->status, ['revoked', 'replaced', 'suspended', 'expired'], true)) {
            return ['valid' => false, 'reason' => 'revoked', 'card' => $card, 'card_status' => strtoupper($card->status)];
        }
        $ok = $card->isValid() && $user && ($user->is_active ?? true);
        return [
            'valid' => $ok,
            'card' => $card,
            'member_name' => $user?->name,
            'member_number' => $user?->member_number,
            'role' => $user?->roleDisplayName(),
            'account' => ($user && ($user->is_active ?? true)) ? 'ACTIVE' : 'INACTIVE',
            'identity' => $user?->identity_status === 'verified' ? 'VERIFIED' : strtoupper((string) $user?->identity_status),
            'card_status' => strtoupper($card->status),
        ];
    }

    public function verificationUrl(string $token): string
    {
        return route('verify.member', $token);
    }
}
