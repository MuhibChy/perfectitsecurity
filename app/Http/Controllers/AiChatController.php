<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Services\Ai\AiChatService;
use App\Services\Ai\AiAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    private AiChatService $chatService;

    public function __construct()
    {
        $this->chatService = app(AiChatService::class);
    }

    /**
     * Start a new AI chat conversation.
     */
    public function startConversation(Request $request)
    {
        // Rate limit: 10 starts per minute per IP
        if (RateLimiter::tooManyAttempts('ai:start:' . $request->ip(), 10)) {
            return response()->json(['error' => 'Too many requests. Please try again later.'], 429);
        }
        RateLimiter::hit('ai:start:' . $request->ip(), 60);

        $user = $request->user();

        if (!$user) {
            $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
            ]);
        }

        $conversation = $this->chatService->startConversation(
            $user,
            $request->guest_name,
            $request->guest_email,
            $request->source ?? 'public'
        );

        $suggestedQuestions = $this->chatService->getSuggestedQuestions($user);

        return response()->json([
            'conversation_id' => $conversation->id,
            'session_id' => $conversation->session_id,
            'suggested_questions' => $suggestedQuestions,
            'welcome_message' => $this->getWelcomeMessage($user),
        ]);
    }

    /**
     * Send a message to the AI chat.
     */
    public function sendMessage(Request $request)
    {
        // Rate limit: 30 messages per minute per IP
        if (RateLimiter::tooManyAttempts('ai:chat:' . $request->ip(), 30)) {
            return response()->json(['error' => 'Too many messages. Please slow down.'], 429);
        }
        RateLimiter::hit('ai:chat:' . $request->ip(), 60);

        $request->validate([
            'conversation_id' => 'required|exists:ai_conversations,id',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = AiConversation::findOrFail($request->conversation_id);
        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }

        // Check conversation is active
        if ($conversation->status !== 'active') {
            return response()->json(['error' => 'This conversation is no longer active.'], 400);
        }

        // Authorization: guest conversations can only access their own
        if (!$request->user()) {
            $sessionId = $request->header('X-Session-ID');
            if ($conversation->session_id !== $sessionId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif ($conversation->user_id && $conversation->user_id !== $request->user()->id) {
            // Staff can view any conversation
            if (!$request->user()->isStaff()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $result = $this->chatService->processMessage($conversation, $request->message);

        return response()->json($result);
    }

    /**
     * Confirm and create a ticket from AI draft.
     */
    public function confirmTicket(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:ai_conversations,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent,critical',
        ]);

        $conversation = AiConversation::findOrFail($request->conversation_id);
        $user = $request->user();

        if (!$user || !$user->isCustomer()) {
            return response()->json(['error' => 'Please log in to create a ticket.'], 401);
        }
        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }

        $result = $this->chatService->createTicketFromDraft($conversation, [
            'subject' => $request->subject,
            'description' => $request->description,
            'category' => $request->category ?? 'General Support',
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json($result);
    }

    /**
     * Request escalation to human support.
     */
    public function escalate(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:ai_conversations,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $conversation = AiConversation::findOrFail($request->conversation_id);
        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }
        $escalation = $conversation->escalate($request->reason ?? 'Customer requested human support');

        return response()->json([
            'success' => true,
            'message' => 'A support agent will be with you shortly. Your conversation has been escalated.',
            'escalation_id' => $escalation->id,
        ]);
    }

    /**
     * Get conversation history.
     */
    public function getMessages(Request $request, $conversationId)
    {
        $conversation = AiConversation::findOrFail($conversationId);

        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }

        $messages = $conversation->messages()->orderBy('created_at')->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at,
            ]),
        ]);
    }

    /**
     * Close a conversation.
     */
    public function close(Request $request, $conversationId)
    {
        $conversation = AiConversation::findOrFail($conversationId);
        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }
        $conversation->close();

        return response()->json(['success' => true, 'message' => 'Conversation closed.']);
    }

    /**
     * Rate conversation satisfaction.
     */
    public function rate(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:ai_conversations,id',
            'satisfied' => 'required|boolean',
        ]);

        $conversation = AiConversation::findOrFail($request->conversation_id);
        if ($response = $this->authorizeConversation($request, $conversation)) {
            return $response;
        }
        $conversation->update(['satisfied' => $request->boolean('satisfied')]);

        return response()->json(['success' => true]);
    }

    /**
     * Get suggested questions.
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'questions' => $this->chatService->getSuggestedQuestions($user),
        ]);
    }

    private function getWelcomeMessage($user): string
    {
        $name = $user ? $user->name : 'there';
        // Live service categories — never hard-code imaginary services.
        try {
            $services = \App\Models\ServiceCategory::where('is_active', true)
                ->orderBy('sort_order')->limit(6)->pluck('name')->all();
        } catch (\Throwable $e) {
            $services = [];
        }
        $serviceList = $services
            ? implode(', ', $services)
            : 'IT support, cybersecurity, cloud, and software services';

        return "Hi {$name}! I'm the " . config('app.name') . " AI Support Assistant. "
            . "I can help you understand our services ({$serviceList}), explain how quoting and support work, "
            . "and guide you toward the right service or request.\n\n"
            . "General questions need no account. If you'd like me to submit a ticket or request for you, "
            . "I'll ask you to sign in first so your identity is verified.\n\n"
            . "How can I help you today?";
    }

    /** Return a response only when the requester does not own this conversation. */
    private function authorizeConversation(Request $request, AiConversation $conversation): ?\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if ($user) {
            if ($user->isStaff() || $conversation->user_id === $user->id) {
                return null;
            }
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId || !hash_equals((string) $conversation->session_id, (string) $sessionId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return null;
    }
}
