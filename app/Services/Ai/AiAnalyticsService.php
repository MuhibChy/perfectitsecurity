<?php

namespace App\Services\Ai;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsageRecord;
use App\Models\AiKnowledgeGap;
use App\Models\KbArticle;
use Carbon\Carbon;

class AiAnalyticsService
{
    /**
     * Get AI support dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth();

        return [
            'total_conversations' => AiConversation::count(),
            'active_conversations' => AiConversation::active()->count(),
            'conversations_today' => AiConversation::whereDate('created_at', $today)->count(),
            'total_messages' => AiMessage::count(),
            'escalated_conversations' => AiConversation::where('status', 'escalated')->count(),
            'tickets_created' => AiConversation::whereNotNull('related_ticket_id')->count(),
            'resolved_conversations' => AiConversation::where('status', 'closed')->where('satisfied', true)->count(),
            'total_cost' => AiUsageRecord::sum('cost'),
            'cost_this_month' => AiUsageRecord::where('recorded_date', '>=', $thisMonth)->sum('cost'),
            'cost_today' => AiUsageRecord::where('recorded_date', $today)->sum('cost'),
            'total_tokens' => AiUsageRecord::sum('input_tokens') + AiUsageRecord::sum('output_tokens'),
            'knowledge_gaps' => AiKnowledgeGap::where('status', 'detected')->count(),
        ];
    }

    /**
     * Get most common questions asked to AI.
     */
    public function getMostCommonQuestions(int $limit = 20): array
    {
        $questions = AiMessage::where('role', 'user')
            ->selectRaw('content, COUNT(*) as count')
            ->groupBy('content')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $questions->toArray();
    }

    /**
     * Get unanswered questions (potential knowledge gaps).
     */
    public function getUnansweredQuestions(int $limit = 20): array
    {
        return AiKnowledgeGap::where('status', 'detected')
            ->orderByDesc('occurrence_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get escalation rate.
     */
    public function getEscalationRate(): float
    {
        $total = AiConversation::count();
        if ($total === 0) return 0;

        $escalated = AiConversation::where('status', 'escalated')->count();
        return round(($escalated / $total) * 100, 1);
    }

    /**
     * Get AI resolution rate (conversations closed without escalation).
     */
    public function getResolutionRate(): float
    {
        $total = AiConversation::where('status', 'closed')->count();
        if ($total === 0) return 0;

        $resolved = AiConversation::where('status', 'closed')
            ->where('satisfied', true)
            ->count();
        return round(($resolved / $total) * 100, 1);
    }

    /**
     * Get most used KB articles by AI.
     */
    public function getMostUsedArticles(int $limit = 10): array
    {
        // Parse sources from AI messages to find most cited articles
        $messages = AiMessage::whereNotNull('sources')
            ->where('role', 'assistant')
            ->get();

        $articleCounts = [];
        foreach ($messages as $message) {
            if (is_array($message->sources)) {
                foreach ($message->sources as $articleId) {
                    $articleCounts[$articleId] = ($articleCounts[$articleId] ?? 0) + 1;
                }
            }
        }

        arsort($articleCounts);
        $topIds = array_slice(array_keys($articleCounts), 0, $limit);

        if (empty($topIds)) return [];

        return KbArticle::whereIn('id', $topIds)
            ->with('category')
            ->get()
            ->map(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category->name,
                'usage_count' => $articleCounts[$article->id] ?? 0,
            ])
            ->toArray();
    }

    /**
     * Get daily usage for the last N days.
     */
    public function getDailyUsage(int $days = 30): array
    {
        return AiUsageRecord::selectRaw('recorded_date, SUM(cost) as cost, COUNT(*) as requests, SUM(input_tokens + output_tokens) as tokens')
            ->where('recorded_date', '>=', now()->subDays($days))
            ->groupBy('recorded_date')
            ->orderBy('recorded_date')
            ->get()
            ->toArray();
    }

    /**
     * Detect knowledge gaps from unanswered AI responses.
     */
    public function detectKnowledgeGap(string $question, ?string $aiResponse = null): void
    {
        // Check if this question already exists as a gap
        $existingGap = AiKnowledgeGap::where('question', $question)->first();

        if ($existingGap) {
            $existingGap->incrementOccurrence();
            if ($aiResponse) {
                $answers = $existingGap->sample_answers ?? [];
                $answers[] = $aiResponse;
                $existingGap->update(['sample_answers' => array_slice($answers, -5)]);
            }
        } else {
            AiKnowledgeGap::create([
                'question' => $question,
                'sample_answers' => $aiResponse ? [$aiResponse] : [],
            ]);
        }
    }

    /**
     * Get usage limits status.
     */
    public function checkUsageLimits(string $limitType = 'daily'): array
    {
        $limits = [
            'daily' => [
                'limit' => (int) AiSetting::get('daily_message_limit', 1000),
                'current' => AiMessage::whereDate('created_at', now()->toDateString())->where('role', 'user')->count(),
            ],
            'monthly' => [
                'limit' => (int) AiSetting::get('monthly_message_limit', 30000),
                'current' => AiMessage::where('created_at', '>=', now()->startOfMonth())->where('role', 'user')->count(),
            ],
            'daily_cost' => [
                'limit' => (float) AiSetting::get('daily_cost_limit', 50.00),
                'current' => AiUsageRecord::getDailyCost(),
            ],
            'monthly_cost' => [
                'limit' => (float) AiSetting::get('monthly_cost_limit', 500.00),
                'current' => AiUsageRecord::getMonthlyCost(),
            ],
        ];

        $limit = $limits[$limitType] ?? $limits['daily'];
        $limit['percentage'] = $limit['limit'] > 0 ? round(($limit['current'] / $limit['limit']) * 100, 1) : 0;
        $limit['exceeded'] = $limit['current'] >= $limit['limit'];

        return $limit;
    }
}
