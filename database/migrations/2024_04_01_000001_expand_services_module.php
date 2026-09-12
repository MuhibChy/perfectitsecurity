<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Countries table
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique(); // ISO code like UK, US, BD
            $table->string('currency_code', 3); // GBP, USD, BDT
            $table->string('currency_symbol', 5); // £, $, ৳
            $table->string('currency_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Enhance services table
        Schema::table('services', function (Blueprint $table) {
            $table->string('subcategory')->nullable()->after('slug');
            $table->text('full_description')->nullable()->after('description');
            $table->text('deliverables')->nullable()->after('full_description'); // JSON array
            $table->text('scope')->nullable()->after('deliverables');
            $table->text('exclusions')->nullable()->after('scope');
            $table->text('process_steps')->nullable()->after('exclusions'); // JSON array
            $table->enum('complexity_level', ['basic', 'standard', 'advanced', 'enterprise'])->default('standard')->after('price_type');
            $table->string('seo_title')->nullable()->after('sort_order');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('tags')->nullable()->after('seo_description'); // JSON array
        });

        // Service Country Prices
        Schema::create('service_country_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->enum('pricing_type', ['fixed', 'starting_from', 'hourly', 'daily', 'monthly', 'recurring', 'custom_quote'])->default('fixed');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->date('discount_valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_id', 'country_id']);
        });

        // Enhance service_requests table for full quotation workflow
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            $table->string('currency', 3)->nullable()->after('country_id');
            $table->decimal('quoted_price', 12, 2)->nullable()->after('budget');
            $table->string('currency_symbol', 5)->nullable()->after('quoted_price');
            $table->string('assigned_to')->nullable()->after('status');
            $table->text('internal_notes')->nullable()->after('assigned_to');
            $table->string('attachment_paths')->nullable()->after('internal_notes'); // JSON array
            $table->text('scope_details')->nullable()->after('attachment_paths');
            $table->text('exclusions')->nullable()->after('scope_details');
            $table->string('estimated_delivery')->nullable()->after('exclusions');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('estimated_delivery');
            $table->foreignId('quotation_id')->nullable()->after('priority')->constrained()->nullOnDelete();
            $table->enum('review_status', [
                'new', 'under_review', 'awaiting_info', 'scope_clarification',
                'pricing_in_progress', 'pending_approval', 'sent_to_customer',
                'customer_viewed', 'accepted', 'rejected', 'expired', 'cancelled', 'converted'
            ])->default('new')->after('status');
        });
    }

    public function down()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['quotation_id']);
            $table->dropColumn([
                'country_id', 'currency', 'quoted_price', 'currency_symbol',
                'assigned_to', 'internal_notes', 'attachment_paths', 'scope_details',
                'exclusions', 'estimated_delivery', 'priority', 'quotation_id', 'review_status'
            ]);
        });

        Schema::dropIfExists('service_country_prices');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'subcategory', 'full_description', 'deliverables', 'scope',
                'exclusions', 'process_steps', 'complexity_level',
                'seo_title', 'seo_description', 'tags'
            ]);
        });

        Schema::dropIfExists('countries');
    }
};
