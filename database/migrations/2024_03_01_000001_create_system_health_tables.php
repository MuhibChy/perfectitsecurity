<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // System Health Checks Table
        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('check_name');
            $table->string('status')->default('healthy'); // healthy, warning, critical
            $table->integer('response_time_ms')->nullable();
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['module', 'status']);
            $table->index('checked_at');
        });

        // Page Health Table
        Schema::create('page_healths', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->nullable();
            $table->string('uri');
            $table->string('module')->default('General');
            $table->string('method')->default('GET');
            $table->string('role_tested')->default('guest'); // guest, customer, admin
            $table->integer('http_status')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('status')->default('Not Checked'); // healthy, warning, error, unavailable, not_checked
            $table->text('error_summary')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamps();

            $table->index(['module', 'status']);
            $table->index('route_name');
            $table->index('uri');
        });

        // System Error Logs Table
        Schema::create('system_error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module')->default('System');
            $table->string('route')->nullable();
            $table->string('error_type')->default('Exception'); // 404, 500, Database, FrontendJS, Exception
            $table->text('message');
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->text('trace')->nullable();
            $table->string('status')->default('unresolved'); // unresolved, resolved, ignored
            $table->integer('occurrences')->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['module', 'status']);
            $table->index(['error_type', 'status']);
            $table->index('last_seen_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_error_logs');
        Schema::dropIfExists('page_healths');
        Schema::dropIfExists('system_health_checks');
    }
};
