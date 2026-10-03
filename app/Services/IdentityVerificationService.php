<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\IdentityDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Identity verification lifecycle:
 * not_started → submitted → under_review → verified
 * (rejected / resubmission_required / expired / revoked as branches).
 * Uploading NEVER auto-verifies; approval requires an authorized verifier
 * and is always audited. History rows are never overwritten.
 */
class IdentityVerificationService
{
    public const TRANSITIONS = [
        'not_started' => ['submitted'],
        'submitted' => ['under_review', 'rejected'],
        'under_review' => ['verified', 'rejected', 'resubmission_required'],
        'resubmission_required' => ['submitted'],
        'rejected' => ['submitted'],
        'verified' => ['expired', 'revoked'],
        'expired' => ['submitted'],
        'revoked' => ['submitted'],
    ];

    public function submit(User $user, UploadedFile $file, array $meta): IdentityDocument
    {
        // Security decisions read fresh state, never a possibly stale instance.
        $user = $user->fresh() ?? $user;
        abort_unless(in_array($user->identity_status, ['not_started', 'resubmission_required', 'rejected', 'expired', 'revoked'], true),
            422, 'A verification case is already in progress.');
        abort_unless(in_array($meta['document_type'] ?? '', IdentityDocument::TYPES, true), 422, 'Unsupported document type.');

        return DB::transaction(function () use ($user, $file, $meta) {
            $path = $file->store('identity-documents/'.$user->id, 'private');
            $doc = IdentityDocument::create([
                'user_id' => $user->id,
                'document_type' => $meta['document_type'],
                'issuing_country' => isset($meta['issuing_country']) ? strtoupper(substr($meta['issuing_country'], 0, 3)) : null,
                'expiry_date' => $meta['expiry_date'] ?? null,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'submitted',
            ]);
            $this->setUserStatus($user, 'submitted', "Document #{$doc->id} submitted ({$doc->document_type}).");
            \App\Services\ServiceTrackingService::notify($user->id, 'identity_submitted', 'Identity document received', "Your {$doc->document_type} is pending review.");

            return $doc->fresh();
        });
    }

    public function startReview(IdentityDocument $doc, User $reviewer): IdentityDocument
    {
        $this->requireVerifier($reviewer);

        return DB::transaction(function () use ($doc, $reviewer) {
            $doc = IdentityDocument::lockForUpdate()->findOrFail($doc->id);
            abort_unless($doc->status === 'submitted', 422, 'Only submitted documents can enter review.');
            $doc->update(['status' => 'under_review', 'reviewer_id' => $reviewer->id]);
            $this->setUserStatus($doc->user, 'under_review', "Document #{$doc->id} under review by {$reviewer->name}.");
            AuditLog::log('identity.review_started', 'identity', $doc, "Reviewer {$reviewer->name} opened document #{$doc->id} for {$doc->user->name}.");

            return $doc->fresh();
        });
    }

    public function approve(IdentityDocument $doc, User $reviewer): IdentityDocument
    {
        $this->requireVerifier($reviewer);

        return DB::transaction(function () use ($doc, $reviewer) {
            $doc = IdentityDocument::lockForUpdate()->findOrFail($doc->id);
            abort_unless(in_array($doc->status, ['submitted', 'under_review'], true), 422, 'Document cannot be verified from status '.$doc->status.'.');
            $doc->update(['status' => 'verified', 'reviewer_id' => $reviewer->id, 'reviewed_at' => now(), 'rejection_reason' => null]);
            $user = $doc->user;
            $user->forceFill(['identity_status' => 'verified', 'identity_verified_at' => now()])->save();
            AuditLog::log('identity.verified', 'identity', $doc, "Identity of {$user->name} ({$user->member_number}) VERIFIED by {$reviewer->name}.");
            \App\Services\ServiceTrackingService::notify($user->id, 'identity_verified', 'Identity verified', 'Your identity verification is complete.');

            return $doc->fresh();
        });
    }

    public function reject(IdentityDocument $doc, User $reviewer, string $reason, bool $allowResubmit = true): IdentityDocument
    {
        $this->requireVerifier($reviewer);
        abort_if(trim($reason) === '', 422, 'A rejection reason is required.');

        return DB::transaction(function () use ($doc, $reviewer, $reason, $allowResubmit) {
            $doc = IdentityDocument::lockForUpdate()->findOrFail($doc->id);
            abort_unless(in_array($doc->status, ['submitted', 'under_review'], true), 422, 'Document cannot be rejected from status '.$doc->status.'.');
            $doc->update(['status' => 'rejected', 'reviewer_id' => $reviewer->id, 'reviewed_at' => now(), 'rejection_reason' => $reason]);
            $this->setUserStatus($doc->user, $allowResubmit ? 'resubmission_required' : 'rejected', "Document #{$doc->id} rejected by {$reviewer->name}: {$reason}");
            \App\Services\ServiceTrackingService::notify($doc->user_id, 'identity_rejected', 'Identity verification needs attention', $reason);

            return $doc->fresh();
        });
    }

    public function revoke(User $user, User $actor, string $reason): void
    {
        $this->requireVerifier($actor);
        abort_if(trim($reason) === '', 422, 'A revocation reason is required.');
        $user->forceFill(['identity_status' => 'revoked', 'identity_verified_at' => null])->save();
        AuditLog::log('identity.revoked', 'identity', $user, "Identity of {$user->name} REVOKED by {$actor->name}: {$reason}");
        \App\Services\ServiceTrackingService::notify($user->id, 'identity_revoked', 'Identity verification revoked', $reason);
    }

    /** Sensitive document access is always audited; bodies never enter logs. */
    public function recordAccess(IdentityDocument $doc, User $viewer, string $action): void
    {
        AuditLog::log('identity.document_'.$action, 'identity', $doc, "{$viewer->name} ({$viewer->role}) {$action} identity document #{$doc->id} of {$doc->user->name}.");
    }

    protected function setUserStatus(User $user, string $to, string $note): void
    {
        $from = $user->identity_status;
        abort_unless(in_array($to, self::TRANSITIONS[$from] ?? [], true), 422, "Identity cannot move from {$from} to {$to}.");
        $user->forceFill(['identity_status' => $to])->save();
        AuditLog::log('identity.status', 'identity', $user, "Identity {$from} → {$to} for {$user->name}. {$note}");
    }

    protected function requireVerifier(User $user): void
    {
        // Verification authority: admins only (never ordinary employees).
        abort_unless($user->isAdmin(), 403, 'Identity verification requires administrator authorization.');
    }
}
