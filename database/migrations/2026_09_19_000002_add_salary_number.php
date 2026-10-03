<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll document numbering (§18/§29): every salary row gets a unique
 * SAL- reference for payslips, statements and reconciliation.
 * Additive only: nullable column + backfill, no rewrite of history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->string('salary_number')->nullable()->unique()->after('id');
        });

        // Backfill pre-existing rows deterministically (SAL-<id padded>).
        foreach (\App\Models\Salary::whereNull('salary_number')->orderBy('id')->get() as $salary) {
            $salary->update(['salary_number' => 'SAL-'.str_pad((string) $salary->id, 6, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropUnique(['salary_number']);
            $table->dropColumn('salary_number');
        });
    }
};
