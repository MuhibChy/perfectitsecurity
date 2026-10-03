<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flexible multi-provider payment infrastructure (additive only).
 *
 * Reuses existing: payments, invoices, service_orders, receipts,
 * financial_transactions, wallets, commissions, exchange_rates, countries.
 * Adds only the missing provider-management layer:
 * - payment_providers: DB-configured provider registry (credentials stored
 *   encrypted via model mutators — never plaintext, never logged).
 * - bank_accounts: company receiving accounts (BD + international).
 * - payment_transactions: ONE internal record per payment intent with
 *   centralized lifecycle + idempotency keys + FX snapshot + fee split.
 * - payment_webhook_events: per-provider event log with idempotency.
 * - payment_refunds: controlled refund workflow (requested→approved→
 *   processing→refunded/failed); execution reuses existing Payment RFD
 *   rows + FinancialService::recordRefund (no parallel ledger).
 * - manual_bank_payments: customer bank-transfer submissions
 *   (pending_verification→verified/rejected; never auto-paid).
 * - payment_reconciliation_records: internal-vs-provider comparison flags.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique(); // bkash|nagad|rocket|bank_transfer|stripe|card|manual|...
            $table->string('name', 120);
            $table->string('type', 40)->default('gateway'); // mobile_wallet|bank|gateway|card|manual|wallet
            $table->string('country', 100)->nullable(); // Bangladesh|United Kingdom|International
            $table->json('currencies')->nullable(); // ["BDT"] / ["GBP","USD","EUR"]
            $table->json('payment_methods')->nullable(); // ["checkout","tokenized","refund"]
            $table->string('environment', 10)->default('test'); // test|live
            $table->string('status', 20)->default('disabled'); // enabled|disabled|test|live|maintenance
            $table->unsignedInteger('priority')->default(100); // lower = preferred
            $table->decimal('min_amount', 14, 2)->nullable();
            $table->decimal('max_amount', 14, 2)->nullable();
            $table->string('fee_type', 20)->nullable(); // fixed|percent|mixed|none
            $table->decimal('fee_value', 14, 4)->nullable();
            $table->decimal('platform_fee_value', 14, 4)->nullable();
            $table->text('credentials')->nullable(); // ENCRYPTED JSON (api key/secret/merchant id...)
            $table->text('webhook_secret')->nullable(); // ENCRYPTED
            $table->string('webhook_url', 255)->nullable();
            $table->json('config')->nullable(); // non-secret provider config
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'priority']);
            $table->index(['country', 'status']);
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('label', 120);
            $table->string('country', 100)->default('Bangladesh');
            $table->char('currency', 3)->default('BDT');
            $table->string('account_name', 150);
            $table->string('bank_name', 150);
            $table->string('branch', 150)->nullable();
            $table->text('account_number')->nullable(); // ENCRYPTED at rest
            $table->string('routing_number', 60)->nullable();
            $table->string('swift_bic', 20)->nullable();
            $table->string('iban', 60)->nullable();
            $table->text('payment_instructions')->nullable();
            $table->string('purpose', 60)->default('collections'); // collections|payroll|general
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'currency']);
            $table->index(['is_active', 'country']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique(); // PTXN-YYYYMMDD-XXXXXX
            $table->string('idempotency_key', 80)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('payment_providers')->nullOnDelete();
            $table->string('provider_key', 60)->default('manual');
            $table->string('payment_method', 60)->default('manual'); // card|bkash|nagad|rocket|bank_transfer|wallet|cash
            $table->string('provider_reference', 160)->nullable()->unique();
            $table->string('provider_event_id', 160)->nullable()->unique();
            // Money snapshot: original invoice currency is NEVER overwritten.
            $table->decimal('original_amount', 14, 2);
            $table->char('original_currency', 3);
            $table->decimal('exchange_rate', 16, 8)->nullable();
            $table->decimal('converted_amount', 14, 2)->nullable();
            $table->char('settlement_currency', 3)->nullable();
            $table->char('provider_currency', 3)->nullable();
            $table->decimal('provider_amount', 14, 2)->nullable();
            // Fee split: gross - provider_fee - platform_fee = net.
            $table->decimal('gross_amount', 14, 2);
            $table->decimal('provider_fee', 14, 2)->default(0);
            $table->decimal('platform_fee', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2);
            $table->string('status', 30)->default('initiated');
            // initiated|pending|processing|authorized|paid|failed|cancelled|refunded|partially_refunded|pending_verification|verified|rejected|requires_verification
            $table->timestamp('paid_at')->nullable();
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['invoice_id', 'status']);
            $table->index(['provider_key', 'status']);
            $table->index('status');
        });

        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key', 60);
            $table->string('event_id', 160)->unique();
            $table->string('transaction_reference', 40)->nullable();
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->string('status', 20)->default('received'); // received|processing|processed|duplicate|failed
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['provider_key', 'status']);
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 40)->unique(); // RFND-YYYYMMDD-XXXXXX
            $table->string('idempotency_key', 80)->unique();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->string('provider_refund_id', 160)->nullable();
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('requested'); // requested|approved|processing|refunded|failed|rejected
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('manual_bank_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique(); // MBP-YYYYMMDD-XXXXXX
            $table->string('idempotency_key', 80)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->string('sender_name', 150)->nullable();
            $table->string('sender_bank', 150)->nullable();
            $table->string('transfer_reference', 120)->nullable();
            $table->string('provider_transaction_id', 160)->nullable();
            $table->date('transferred_at')->nullable();
            $table->string('receipt_path', 255)->nullable();
            $table->string('status', 30)->default('pending_verification'); // pending_verification|verified|rejected
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('payment_reconciliation_records', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key', 60);
            $table->string('internal_reference', 40);
            $table->string('provider_reference', 160)->nullable();
            $table->decimal('internal_amount', 14, 2);
            $table->char('internal_currency', 3);
            $table->decimal('provider_amount', 14, 2)->nullable();
            $table->char('provider_currency', 3)->nullable();
            $table->string('internal_status', 30);
            $table->string('provider_status', 60)->nullable();
            $table->string('result', 20)->default('matched'); // matched|mismatch|requires_verification
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            $table->index(['result', 'checked_at']);
            $table->index('internal_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reconciliation_records');
        Schema::dropIfExists('manual_bank_payments');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('payment_providers');
    }
};
