<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiKnowledgeGap;
use App\Models\AiSetting;
use App\Models\AiUsageRecord;
use App\Services\Ai\AiAnalyticsService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    private AiAnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = app(AiAnalyticsService::class);
    }

    /**
     * AI Dashboard overview.
     */
    public function index()
    {
        $stats = $this->analyticsService->getDashboardStats();
        $dailyUsage = $this->analyticsService->getDailyUsage(30);
        $escalationRate = $this->analyticsService->getEscalationRate();
        $resolutionRate = $this->analyticsService->getResolutionRate();
        $mostUsedArticles = $this->analyticsService->getMostUsedArticles(5);
        $recentConversations = AiConversation::with('user')->latest()->limit(10)->get();

        return view('admin.ai.index', compact(
            'stats', 'dailyUsage', 'escalationRate', 'resolutionRate',
            'mostUsedArticles', 'recentConversations'
        ));
    }

    /**
     * View AI conversations list.
     */
    public function conversations(Request $request)
    {
        $query = AiConversation::with('user');

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('guest_name', 'like', "%{$request->search}%")
                  ->orWhere('guest_email', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$request->search}%"));
            });
        }

        $conversations = $query->latest()->paginate(20);

        return view('admin.ai.conversations', compact('conversations'));
    }

    /**
     * View a single conversation with messages.
     */
    public function showConversation($id)
    {
        $conversation = AiConversation::with('user', 'messages', 'escalations', 'relatedTicket')->findOrFail($id);
        $messages = $conversation->messages()->orderBy('created_at')->get();

        return view('admin.ai.conversation-show', compact('conversation', 'messages'));
    }

    /**
     * View knowledge gaps.
     */
    public function knowledgeGaps()
    {
        $gaps = AiKnowledgeGap::latest()->paginate(20);

        return view('admin.ai.knowledge-gaps', compact('gaps'));
    }

    /**
     * Update knowledge gap status.
     */
    public function updateGap(Request $request, $id)
    {
        $gap = AiKnowledgeGap::findOrFail($id);
        $request->validate(['status' => 'required|in:detected,reviewing,resolved,dismissed']);

        $gap->update([
            'status' => $request->status,
            'resolved_by' => $request->status === 'resolved' ? auth()->id() : $gap->resolved_by,
        ]);

        return redirect()->back()->with('success', 'Knowledge gap updated!');
    }

    /**
     * View AI settings.
     */
    public function settings()
    {
        $settings = AiSetting::pluck('value', 'key')->toArray();

        return view('admin.ai.settings', compact('settings'));
    }

    /**
     * Update AI settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'ai_provider' => 'nullable|string',
            'ai_model' => 'nullable|string',
            'ai_enabled' => 'boolean',
            'ai_system_prompt' => 'nullable|string',
            'daily_message_limit' => 'nullable|integer|min:0',
            'monthly_message_limit' => 'nullable|integer|min:0',
            'daily_cost_limit' => 'nullable|numeric|min:0',
            'monthly_cost_limit' => 'nullable|numeric|min:0',
            'escalation_enabled' => 'boolean',
            'ticket_creation_enabled' => 'boolean',
            'rate_limit_per_minute' => 'nullable|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            AiSetting::set($key, $value);
        }

        return redirect()->route('admin.ai.settings')->with('success', 'AI settings updated!');
    }

    /**
     * View AI usage records.
     */
    public function usage(Request $request)
    {
        $query = AiUsageRecord::with('user');

        if ($request->date_from) $query->where('recorded_date', '>=', $request->date_from);
        if ($request->date_to) $query->where('recorded_date', '<=', $request->date_to);
        if ($request->provider) $query->where('provider', $request->provider);
        if ($request->success !== null) $query->where('success', $request->boolean('success'));

        $records = $query->latest()->paginate(30);

        $totalCost = $records->sum('cost');
        $totalTokens = $records->sum('input_tokens') + $records->sum('output_tokens');

        return view('admin.ai.usage', compact('records', 'totalCost', 'totalTokens'));
    }

    /**
     * Most common questions analytics.
     */
    public function questions()
    {
        $questions = $this->analyticsService->getMostCommonQuestions(50);

        return view('admin.ai.questions', compact('questions'));
    }
}
