<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_otp_hash')->nullable()->after('verification_status');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_hash');
            $table->unsignedTinyInteger('phone_otp_attempts')->default(0)->after('phone_otp_expires_at');
            $table->timestamp('phone_otp_sent_at')->nullable()->after('phone_otp_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_otp_hash',
                'phone_otp_expires_at',
                'phone_otp_attempts',
                'phone_otp_sent_at',
            ]);
        });
    }
};
