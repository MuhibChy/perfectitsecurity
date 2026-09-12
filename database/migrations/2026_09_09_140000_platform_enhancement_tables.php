<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Quotation schema alignment ---
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'service_request_id')) {
                $table->foreignId('service_request_id')->nullable()->after('company_id')->constrained('service_requests')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total');
            }
            if (!Schema::hasColumn('quotations', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('currency')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('country_id')->constrained('users')->nullOnDelete();
            }
        });

        // --- Quote / lead intake fields ---
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'subject')) {
                $table->string('subject')->nullable()->after('company');
            }
            if (!Schema::hasColumn('service_requests', 'service_interest')) {
                $table->string('service_interest')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('service_requests', 'budget_range')) {
                $table->string('budget_range')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('service_requests', 'timeline')) {
                $table->string('timeline')->nullable()->after('budget_range');
            }
            if (!Schema::hasColumn('service_requests', 'lead_source')) {
                $table->string('lead_source')->nullable()->default('website')->after('timeline');
            }
        });

        // --- Country tax / business hours ---
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('currency_name');
            }
            if (!Schema::hasColumn('countries', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('tax_rate');
            }
            if (!Schema::hasColumn('countries', 'business_hours_start')) {
                $table->time('business_hours_start')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('countries', 'business_hours_end')) {
                $table->time('business_hours_end')->nullable()->after('business_hours_start');
            }
            if (!Schema::hasColumn('countries', 'business_days')) {
                $table->json('business_days')->nullable()->after('business_hours_end');
            }
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->unique(['country_id', 'date']);
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('USD');
            $table->string('target_currency', 3);
            $table->decimal('rate', 16, 8);
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            $table->unique(['base_currency', 'target_currency']);
        });

        // --- CRM leads + activities ---
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('new'); // new, contacted, qualified, proposal, won, lost
            $table->string('priority')->default('medium');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'assigned_to']);
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('note'); // note, call, email, meeting, status_change
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->timestamps();
        });

        // --- Proposals ---
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('draft'); // draft, sent, viewed, accepted, rejected, revised
            $table->unsignedInteger('version')->default(1);
            $table->text('scope_of_work')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('timeline')->nullable();
            $table->text('terms')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proposal_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('heading');
            $table->longText('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('proposal_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // --- Contracts ---
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('draft'); // draft, sent, active, expired, terminated
            $table->longText('body')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // --- Recurring billing / subscriptions ---
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('active'); // trial, active, past_due, paused, cancelled
            $table->string('interval')->default('monthly'); // monthly, yearly
            $table->unsignedInteger('interval_count')->default(1);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('next_billing_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'next_billing_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total');
            }
            if (!Schema::hasColumn('invoices', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('quotation_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'stripe_checkout_session_id')) {
                $table->string('stripe_checkout_session_id')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'stripe_checkout_session_id')) {
                $table->string('stripe_checkout_session_id')->nullable()->after('gateway');
            }
            if (!Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'preferred_currency')) {
                $table->string('preferred_currency', 3)->nullable()->after('country');
            }
            if (!Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale', 5)->nullable()->after('preferred_currency');
            }
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('preferred_locale');
            }
        });

        // --- Marketing content ---
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('client_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('country')->nullable();
            $table->string('summary')->nullable();
            $table->longText('challenge')->nullable();
            $table->longText('solution')->nullable();
            $table->longText('results')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('career_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->string('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('client_name')->nullable();
            $table->string('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('project_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('career_posts');
        Schema::dropIfExists('case_studies');

        Schema::table('users', function (Blueprint $table) {
            foreach (['two_factor_confirmed_at', 'preferred_currency', 'preferred_locale', 'stripe_customer_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach (['stripe_checkout_session_id', 'stripe_payment_intent_id'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'subscription_id')) {
                $table->dropConstrainedForeignId('subscription_id');
            }
            foreach (['currency', 'stripe_checkout_session_id', 'stripe_payment_intent_id'] as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('proposal_versions');
        Schema::dropIfExists('proposal_sections');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('holidays');

        Schema::table('countries', function (Blueprint $table) {
            foreach (['tax_rate', 'timezone', 'business_hours_start', 'business_hours_end', 'business_days'] as $col) {
                if (Schema::hasColumn('countries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_requests', function (Blueprint $table) {
            foreach (['subject', 'service_interest', 'budget_range', 'timeline', 'lead_source'] as $col) {
                if (Schema::hasColumn('service_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'service_request_id')) {
                $table->dropConstrainedForeignId('service_request_id');
            }
            if (Schema::hasColumn('quotations', 'country_id')) {
                $table->dropConstrainedForeignId('country_id');
            }
            if (Schema::hasColumn('quotations', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }
            if (Schema::hasColumn('quotations', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
