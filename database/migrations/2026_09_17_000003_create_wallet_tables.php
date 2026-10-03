<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('wallet_reference')->unique();
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('active');
            $table->decimal('balance', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'currency']);
            $table->index(['status', 'currency']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('transaction_reference')->unique();
            $table->string('type');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('status')->default('completed');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('provider_event_id')->nullable()->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'status', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
