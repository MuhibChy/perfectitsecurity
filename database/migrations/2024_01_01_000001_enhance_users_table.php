<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'admin', 'finance_manager', 'support_manager', 'support_agent', 'project_manager', 'employee', 'freelancer', 'commission_agent', 'sales_agent', 'customer'])->default('customer')->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->string('company_name')->nullable()->after('avatar');
            $table->text('address')->nullable()->after('company_name');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->string('country')->nullable()->after('zip_code');
            $table->boolean('is_active')->default(true)->after('country');
            $table->boolean('two_factor_enabled')->default(false)->after('is_active');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_secret');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete()->after('last_login_ip');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'role', 'avatar', 'company_name', 'address',
                'city', 'state', 'zip_code', 'country', 'is_active',
                'two_factor_enabled', 'two_factor_secret', 'last_login_at',
                'last_login_ip', 'company_id',
            ]);
        });
    }
};
