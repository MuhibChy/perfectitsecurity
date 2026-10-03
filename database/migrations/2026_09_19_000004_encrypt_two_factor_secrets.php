<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypt TOTP secrets at rest (§50/rule 4). Existing plaintext secrets are
 * re-encrypted in place so enrolled staff keep working — no forced reset.
 * Recovery-code column added (hashes only, never plaintext).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_recovery_codes')) {
            \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
            });
        }

        foreach (\App\Models\User::whereNotNull('two_factor_secret')->get(['id', 'two_factor_secret']) as $user) {
            $raw = $user->getAttributes()['two_factor_secret'];
            if (!is_string($raw) || $raw === '') continue;
            try {
                Crypt::decryptString($raw);
                continue; // already encrypted
            } catch (\Throwable $e) {
                \App\Models\User::where('id', $user->id)->update(['two_factor_secret' => Crypt::encryptString($raw)]);
            }
        }
    }

    public function down(): void
    {
        // Secrets stay encrypted on rollback (never decrypt into plaintext at rest).
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('two_factor_recovery_codes');
        });
    }
};
