<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hidden demo-data flag: machine-readable sample identification that is
 * never displayed to visitors. Display titles/names are realistic;
 * is_demo preserves cleanup safety, test targeting, and auditability.
 * All columns nullable-boolean, default false — fully additive.
 */
return new class extends Migration
{
    private const TABLES = [
        'users',
        'companies',
        'leads',
        'service_requests',
        'services',
        'quotations',
        'proposals',
        'contracts',
        'service_orders',
        'projects',
        'tasks',
        'tickets',
        'kb_articles',
        'invoices',
        'payments',
        'expenses',
        'notifications',
        'customer_documents',
        'portfolio_items',
        'case_studies',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'is_demo')) {
                    $t->boolean('is_demo')->default(false)->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'is_demo')) {
                    $t->dropColumn('is_demo');
                }
            });
        }
    }
};
