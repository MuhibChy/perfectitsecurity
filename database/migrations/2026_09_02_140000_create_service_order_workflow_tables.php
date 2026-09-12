<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('verification_status')->default('pending')->after('phone_verified_at');
        });

        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('customer_portal');
            $table->text('requirements');
            $table->string('priority')->default('medium');
            $table->string('status')->default('draft');
            $table->string('payment_authorization')->default('not_authorized');
            $table->string('currency', 3)->default('USD');
            $table->decimal('original_price', 12, 2)->default(0);
            $table->decimal('final_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('expected_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->timestamp('customer_accepted_at')->nullable();
            $table->timestamp('employee_approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('service_order_price_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind');
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->text('terms')->nullable();
            $table->string('status')->default('proposed');
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('issued_at');
            $table->timestamps();
        });

        foreach (['invoices', 'payments', 'tickets', 'tasks', 'expenses', 'financial_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('service_order_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['financial_transactions', 'expenses', 'tasks', 'tickets', 'payments', 'invoices'] as $tableName) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropConstrainedForeignId('service_order_id'));
        }
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('service_order_price_revisions');
        Schema::dropIfExists('service_orders');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at', 'verification_status']);
        });
    }
};
