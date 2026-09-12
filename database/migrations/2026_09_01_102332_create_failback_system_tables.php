<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CONFIGURATION VERSIONS TABLE
        Schema::create('config_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_number', 50)->unique();
            $table->enum('config_type', ['full', 'partial', 'emergency'])->default('full');
            $table->text('files')->nullable(); // JSON stored as text
            $table->string('database_schema', 255)->nullable();
            $table->text('environment_variables')->nullable(); // JSON stored as text
            $table->text('routes')->nullable(); // JSON stored as text
            $table->text('middleware')->nullable(); // JSON stored as text
            $table->text('services')->nullable(); // JSON stored as text
            $table->text('controllers')->nullable(); // JSON stored as text
            $table->text('models')->nullable(); // JSON stored as text
            $table->text('migrations_applied')->nullable(); // JSON stored as text
            $table->text('composer_packages')->nullable(); // JSON stored as text
            $table->text('npm_packages')->nullable(); // JSON stored as text
            $table->string('checksum', 255);
            $table->enum('status', ['active', 'stable', 'unstable', 'failed'])->default('stable');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('rollback_triggered_at')->nullable();
            $table->text('rollback_reason')->nullable();
            
            // Indexes
            $table->index('version_number', 'idx_config_versions_version');
            $table->index('status', 'idx_config_versions_status');
            $table->index('created_at', 'idx_config_versions_created');
        });

        // 2. BACKUP POINTS TABLE
        Schema::create('backup_points', function (Blueprint $table) {
            $table->id();
            $table->string('backup_id', 50)->unique();
            $table->enum('backup_type', ['auto', 'manual', 'pre_deploy', 'post_deploy', 'emergency'])->default('auto');
            $table->string('backup_path', 255);
            $table->string('database_backup_path', 255)->nullable();
            $table->string('files_backup_path', 255);
            $table->string('assets_backup_path', 255)->nullable();
            $table->foreignId('config_version_id')->constrained('config_versions')->onDelete('cascade');
            $table->bigInteger('size_bytes')->default(0);
            $table->enum('status', ['active', 'restored', 'deleted'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('restored_at')->nullable();
            $table->string('restored_to_version', 50)->nullable();
            $table->text('notes')->nullable();
            
            // Indexes
            $table->index('backup_id', 'idx_backup_points_backup_id');
            $table->index('status', 'idx_backup_points_status');
            $table->index('created_at', 'idx_backup_points_created');
        });

        // 3. HEALTH CHECK LOGS TABLE
        Schema::create('health_check_logs', function (Blueprint $table) {
            $table->id();
            $table->string('check_name', 100);
            $table->enum('check_type', ['http', 'database', 'queue', 'cache', 'api', 'service']);
            $table->string('endpoint', 255)->nullable();
            $table->integer('status_code')->nullable();
            $table->integer('response_time')->nullable();
            $table->boolean('is_healthy')->default(false);
            $table->text('error_message')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('check_name', 'idx_health_check_logs_check_name');
            $table->index('is_healthy', 'idx_health_check_logs_is_healthy');
            $table->index('checked_at', 'idx_health_check_logs_checked');
        });

        // 4. ROLLBACK HISTORY TABLE
        Schema::create('rollback_history', function (Blueprint $table) {
            $table->id();
            $table->string('rollback_id', 50)->unique();
            $table->string('from_version', 50);
            $table->string('to_version', 50);
            $table->enum('rollback_type', ['auto', 'manual', 'emergency'])->default('manual');
            $table->foreignId('triggered_by')->constrained('users')->onDelete('cascade');
            $table->text('trigger_reason');
            $table->foreignId('backup_point_id')->constrained('backup_points')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->text('steps_taken')->nullable(); // JSON stored as text
            $table->text('errors_encountered')->nullable(); // JSON stored as text
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('rollback_id', 'idx_rollback_history_rollback_id');
            $table->index('status', 'idx_rollback_history_status');
            $table->index('created_at', 'idx_rollback_history_created');
        });

        // 5. FAILBACK SETTINGS TABLE
        Schema::create('failback_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        // 6. FAILBACK NOTIFICATIONS TABLE
        Schema::create('failback_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('notification_type', ['health_check_failed', 'rollback_triggered', 'rollback_success', 'rollback_failed', 'emergency']);
            $table->string('subject', 255);
            $table->text('message');
            $table->string('related_rollback_id', 50)->nullable();
            $table->string('related_backup_id', 50)->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('notification_type', 'idx_failback_notifications_type');
            $table->index('is_sent', 'idx_failback_notifications_is_sent');
        });

        // Insert default failback settings
        DB::table('failback_settings')->insert([
            ['setting_key' => 'auto_rollback_enabled', 'setting_value' => 'true', 'description' => 'Enable automatic rollback on failure detection'],
            ['setting_key' => 'health_check_interval', 'setting_value' => '60', 'description' => 'Health check interval in seconds'],
            ['setting_key' => 'max_rollback_attempts', 'setting_value' => '3', 'description' => 'Maximum automatic rollback attempts'],
            ['setting_key' => 'backup_retention_days', 'setting_value' => '30', 'description' => 'Number of days to keep backups'],
            ['setting_key' => 'rollback_timeout', 'setting_value' => '300', 'description' => 'Rollback timeout in seconds'],
            ['setting_key' => 'emergency_contact_email', 'setting_value' => 'admin@example.com', 'description' => 'Email for emergency notifications'],
            ['setting_key' => 'maintenance_mode_message', 'setting_value' => 'We are restoring the website. Please try again in a few minutes.', 'description' => 'Message shown during maintenance mode'],
            ['setting_key' => 'last_known_good_version', 'setting_value' => 'v1.0.0', 'description' => 'Last known working version'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failback_notifications');
        Schema::dropIfExists('failback_settings');
        Schema::dropIfExists('rollback_history');
        Schema::dropIfExists('health_check_logs');
        Schema::dropIfExists('backup_points');
        Schema::dropIfExists('config_versions');
    }
};

