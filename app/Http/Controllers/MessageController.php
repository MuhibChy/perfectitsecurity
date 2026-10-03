<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Http\Request;

/**
 * Permission-scoped messaging (portal + staff share this controller;
 * route middleware sets the boundary, service enforces recipient rules).
 */
class MessageController extends Controller
{
    public function __construct(private MessagingService $messages)
    {
    }

    public function index()
    {
        $inbox = $this->messages->inbox(auth()->user());
        $unread = DirectMessage::where('recipient_id', auth()->id())->whereNull('read_at')->count();

        return view('messages.index', compact('inbox', 'unread'));
    }

    public function create(Request $request)
    {
        $me = auth()->user();
        // Recipient picker respects permission rules server-side on send too.
        $candidates = $me->isCustomer()
            ? User::whereNotIn('role', ['customer', 'freelancer', 'commission_agent'])->where('is_active', true)->orderBy('name')->limit(50)->get()
            : User::where('is_active', true)->orderBy('name')->limit(50)->get();

        return view('messages.create', compact('candidates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:direct_messages,id',
            // Optional link of this conversation to its business work (§9).
            'related_type' => 'nullable|in:ticket,project,order',
            'related_id' => 'nullable|integer',
        ]);
        $recipient = User::findOrFail($data['recipient_id']);
        // Only staff may flag internal notes.
        $internal = auth()->user()->isStaff() && ! empty($data['is_internal']);
        $related = $this->resolveRelatedLink(auth()->user(), $data['related_type'] ?? null, $data['related_id'] ?? null);
        $message = $this->messages->send(auth()->user(), $recipient, $data['body'], $data['subject'] ?? null, [
            'is_internal' => $internal, 'parent_id' => $data['parent_id'] ?? null,
        ]);
        if ($related) {
            $message->update(['related_type' => $related['type'], 'related_id' => $related['id']]);
        }

        return redirect()->route(request()->route()->getName() === 'portal.messages.store' ? 'portal.messages.show' : 'admin.messages.show', $message->id)
            ->with('success', 'Message sent.');
    }

    /** Resolve + authorize an optional business-work link for a message. */
    protected function resolveRelatedLink(User $me, ?string $type, $id): ?array
    {
        if (! $type || ! $id) {
            return null;
        }
        $map = ['ticket' => \App\Models\Ticket::class, 'project' => \App\Models\Project::class, 'order' => \App\Models\ServiceOrder::class];
        abort_unless(isset($map[$type]), 422, 'Unknown link target.');
        $record = $map[$type]::findOrFail((int) $id);
        // Customers may only link their OWN records; staff boundary applies.
        if ($me->isCustomer()) {
            abort_unless((int) ($record->customer_id ?? 0) === (int) $me->id, 403, 'You may only link your own records.');
        }

        return ['type' => $map[$type], 'id' => $record->getKey()];
    }

    public function show($id)
    {
        $message = DirectMessage::with(['sender', 'recipient'])->findOrFail($id);
        $me = auth()->user();
        abort_unless((int) $message->sender_id === (int) $me->id || (int) $message->recipient_id === (int) $me->id, 403);
        if ($me->isCustomer() && ($message->is_internal || ! $message->is_customer_visible)) {
            abort(403);
        }
        if ((int) $message->recipient_id === (int) $me->id) {
            $this->messages->markRead($message, $me);
        }
        $thread = $this->messages->thread($me, (int) $message->sender_id === (int) $me->id ? $message->recipient : $message->sender);

        return view('messages.show', compact('message', 'thread'));
    }
}
