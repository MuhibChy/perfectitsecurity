<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Hash credentials that predate the User model's hashed password cast.
     *
     * Plaintext credentials can never pass Laravel's password verifier. Hashing
     * their existing value preserves the user's password while making it valid
     * for subsequent logins. Existing bcrypt/Argon hashes are left untouched.
     */
    public function up(): void
    {
        DB::table('users')
            ->select('id', 'password')
            ->orderBy('id')
            ->each(function ($user): void {
                if (empty(password_get_info($user->password)['algo'])) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['password' => Hash::make($user->password)]);
                }
            });
    }

    /**
     * Password hashing is deliberately irreversible.
     */
    public function down(): void
    {
        // No-op.
    }
};
