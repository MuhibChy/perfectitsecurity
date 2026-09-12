<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('backup_id', 64)->unique();
            $table->string('type', 20)->default('full'); // full|db|files|pre_deploy|manual
            $table->string('scope', 20)->default('full'); // full|db|files
            $table->string('status', 20)->default('pending'); // pending|running|verifying|verified|failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage', 20)->default('local'); // local|s3
            $table->string('storage_path', 500)->nullable(); // internal identifier only
            $table->boolean('encrypted')->default(false);
            $table->string('encryption_cipher', 30)->nullable();
            $table->string('encryption_key_fingerprint', 64)->nullable(); // sha256 of key, never the key
            $table->string('checksum', 128)->nullable(); // sha256 manifest
            $table->string('db_version', 100)->nullable();
            $table->string('app_version', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origin', 20)->default('scheduler'); // scheduler|manual|deploy
            $table->text('error_message')->nullable();
            $table->string('verification_status', 20)->default('pending'); // pending|passed|failed
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_detail')->nullable();
            $table->string('restore_test_status', 20)->default('not_run'); // not_run|passed|failed
            $table->timestamp('restore_tested_at')->nullable();
            $table->timestamp('retain_until')->nullable();
            $table->boolean('retention_hold')->default(false);
            $table->timestamp('deleted_at')->nullable(); // soft-delete marker (row kept for audit)
            $table->timestamps();

            $table->index('status', 'idx_backups_status');
            $table->index('type', 'idx_backups_type');
            $table->index('created_at', 'idx_backups_created');
            $table->index('retain_until', 'idx_backups_retain');
        });

        Schema::create('backup_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_id')->constrained('backups')->cascadeOnDelete();
            $table->string('kind', 20); // db|files|manifest
            $table->string('disk', 20)->default('local');
            $table->string('path', 500); // internal storage path, never a public URL
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->boolean('encrypted')->default(false);
            $table->unsignedBigInteger('file_count')->nullable(); // for file archives
            $table->timestamps();

            $table->index('backup_id', 'idx_backup_files_backup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_files');
        Schema::dropIfExists('backups');
    }
};
