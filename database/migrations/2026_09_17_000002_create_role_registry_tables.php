<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Central role registry: one row per internal role key. Historical
        // users keep their users.role string; deprecating a registry row
        // never deletes users or their records.
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('registration_allowed')->default(false);
            $table->boolean('approval_required')->default(true);
            $table->boolean('self_registration')->default(false);
            $table->string('dashboard_route')->nullable();
            $table->string('dashboard_label')->nullable();
            $table->json('permissions')->nullable();
            $table->json('training_slugs')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        // Requested vs assigned role (approval workflow). All nullable and
        // additive: existing users and logins are unaffected.
        Schema::table('users', function (Blueprint $table) {
            $table->string('requested_role')->nullable()->after('role');
            $table->string('role_approval_status')->nullable()->after('requested_role');
            $table->foreignId('role_approved_by')->nullable()->after('role_approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('role_approved_at')->nullable()->after('role_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_approved_by');
            $table->dropColumn(['requested_role', 'role_approval_status', 'role_approved_at']);
        });
        Schema::dropIfExists('roles');
    }
};
