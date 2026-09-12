<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_otp_hash')->nullable();
            $table->dateTime('email_otp_expires_at')->nullable();
            $table->unsignedInteger('email_otp_attempts')->default(0);
            $table->dateTime('email_otp_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_otp_hash',
                'email_otp_expires_at',
                'email_otp_attempts',
                'email_otp_sent_at',
            ]);
        });
    }
};
