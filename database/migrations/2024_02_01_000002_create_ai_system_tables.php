<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // AI Conversations
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->enum('status', ['active', 'escalated', 'closed', 'archived'])->default('active');
            $table->string('source')->default('public'); // public, portal, support
            $table->text('context')->nullable(); // JSON context data
            $table->foreignId('related_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('language', 5)->default('en');
            $table->integer('message_count')->default(0);
            $table->boolean('satisfied')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('session_id');
        });

        // AI Messages
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->text('metadata')->nullable(); // JSON: tokens used, model, sources cited
            $table->text('sources')->nullable(); // JSON: KB article IDs cited
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 8, 6)->default(0);
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
        });

        // AI Escalations
        Schema::create('ai_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason');
            $table->text('context_summary')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // AI Knowledge Gaps
        Schema::create('ai_knowledge_gaps', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->integer('occurrence_count')->default(1);
            $table->text('sample_answers')->nullable(); // JSON: array of attempted answers
            $table->enum('status', ['detected', 'reviewing', 'resolved', 'dismissed'])->default('detected');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_kb_article_id')->nullable()->constrained('kb_articles')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        // AI Usage Records
        Schema::create('ai_usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('provider')->default('openai');
            $table->string('model')->default('gpt-3.5-turbo');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 8, 6)->default(0);
            $table->string('request_type'); // chat, search, categorize
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->date('recorded_date');
            $table->timestamps();

            $table->index(['user_id', 'recorded_date']);
            $table->index('recorded_date');
        });

        // AI Settings
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_settings');
        Schema::dropIfExists('ai_usage_records');
        Schema::dropIfExists('ai_knowledge_gaps');
        Schema::dropIfExists('ai_escalations');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
