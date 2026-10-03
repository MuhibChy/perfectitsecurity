<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('system_instructions');
            $table->json('trigger_keywords')->nullable();
            $table->json('allowed_roles')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->unsignedInteger('version')->default(1);
            $table->string('category')->nullable();
            $table->float('temperature')->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'priority']);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('kb_articles', 'ai_readable')) {
                $table->boolean('ai_readable')->default(true)->after('is_featured');
            }
        });

        $this->seedDefaultSkills();
    }

    public function down(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            if (Schema::hasColumn('kb_articles', 'ai_readable')) {
                $table->dropColumn('ai_readable');
            }
        });
        Schema::dropIfExists('ai_skills');
    }

    private function seedDefaultSkills(): void
    {
        $now = now()->toDateTimeString();
        $skills = [
            [
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'description' => 'General customer questions, service explanations, support guidance, navigation assistance.',
                'system_instructions' => 'You are handling a general customer support enquiry. Explain services and procedures using ONLY approved internal context. Guide the customer to /contact, /get-quote, or /portal/tickets when a human or formal request is needed. Never invent policies.',
                'trigger_keywords' => json_encode(['help', 'support', 'question', 'how do', 'how to', 'contact', 'guide', 'navigate', 'portal']),
                'allowed_roles' => json_encode([]),
                'priority' => 100,
            ],
            [
                'name' => 'IT Support',
                'slug' => 'it-support',
                'description' => 'Windows, Microsoft 365, networking, email troubleshooting, hardware/software support.',
                'system_instructions' => 'You are handling an IT support enquiry (Windows, Microsoft 365, networking, email, hardware, software). Give safe, step-by-step troubleshooting using approved internal context. Never request passwords, credentials, or remote-access secrets. Escalate to a support ticket for account-specific or complex issues.',
                'trigger_keywords' => json_encode(['windows', 'microsoft', 'office', '365', 'outlook', 'email', 'network', 'wifi', 'printer', 'laptop', 'computer', 'software', 'hardware', 'troubleshoot', 'error', 'password reset']),
                'allowed_roles' => json_encode([]),
                'priority' => 90,
            ],
            [
                'name' => 'Cybersecurity Education',
                'slug' => 'cybersecurity-education',
                'description' => 'Defensive security concepts, best practices, secure configuration, awareness.',
                'system_instructions' => 'You are providing defensive cybersecurity education only: concepts, best practices, secure configuration, awareness. Never provide offensive instructions, exploit code, or bypass techniques. Never invent certifications, incidents, or guarantees.',
                'trigger_keywords' => json_encode(['security', 'cybersecurity', 'phishing', 'malware', 'ransomware', 'firewall', 'antivirus', 'secure', 'breach', 'vulnerability', 'awareness', 'mfa', '2fa']),
                'allowed_roles' => json_encode([]),
                'priority' => 90,
            ],
            [
                'name' => 'Service Recommendation',
                'slug' => 'service-recommendation',
                'description' => 'Map customer requirements to services in the approved catalogue.',
                'system_instructions' => 'You are recommending services. Recommend ONLY services present in the approved catalogue context. If no catalogue service fits, say so and offer human assistance. Never invent services or prices; custom needs go to the quotation workflow.',
                'trigger_keywords' => json_encode(['service', 'need', 'recommend', 'looking for', 'website security', 'manage', 'solution', 'which service', 'what service']),
                'allowed_roles' => json_encode([]),
                'priority' => 80,
            ],
            [
                'name' => 'Quote Assistance',
                'slug' => 'quote-assistance',
                'description' => 'Understand quote requirements, collect information, direct to quote workflow.',
                'system_instructions' => 'You are assisting with a quotation request. Collect: organization, users, locations, environment, remote/on-site needs, timeline. Summarize and ask for confirmation before any submission. Never promise a final price.',
                'trigger_keywords' => json_encode(['quote', 'quotation', 'estimate', 'pricing', 'price', 'cost', 'proposal', 'budget']),
                'allowed_roles' => json_encode([]),
                'priority' => 80,
            ],
            [
                'name' => 'Knowledge Base Search',
                'slug' => 'knowledge-search',
                'description' => 'Search internal documentation, summarize approved articles, reference sources.',
                'system_instructions' => 'You are answering from approved internal documentation. Cite article titles provided in context. If the retrieved excerpts do not answer the question, say so plainly instead of guessing.',
                'trigger_keywords' => json_encode(['documentation', 'article', 'guide', 'manual', 'faq', 'knowledge base', 'how does', 'explain']),
                'allowed_roles' => json_encode([]),
                'priority' => 70,
            ],
        ];
        foreach ($skills as $s) {
            DB::table('ai_skills')->updateOrInsert(
                ['slug' => $s['slug']],
                array_merge($s, ['status' => 'enabled', 'version' => 1, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
};
