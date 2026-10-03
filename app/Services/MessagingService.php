<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Permission-scoped internal messaging.
 *
 * Rules (server-side, never UI-only):
 * - customers may message staff only (support/management chain), never other customers
 * - staff may message anyone; freelancers/agents may message staff (management)
 * - internal staff notes (is_internal) are never visible to customers
 */
class MessagingService
{
    public function canMessage(User $sender, User $recipient): bool
    {
        if ((int) $sender->id === (int) $recipient->id) {
            return false;
        }
        if ($sender->isCustomer()) {
            return ! $recipient->isCustomer(); // customer → staff only
        }
        if ($sender->isFreelancer()) {
            return $recipient->isStaff(); // agent/contractor → management only
        }

        return true; // staff → anyone
    }

    public function send(User $sender, User $recipient, string $body, ?string $subject = null, array $options = []): DirectMessage
    {
        abort_unless($this->canMessage($sender, $recipient), 403, 'You are not permitted to message this account.');
        abort_if(trim($body) === '', 422, 'Message body is required.');

        // Ownership check: parent message must belong to the sender/recipient pair,
        // otherwise an ID change could attach to (or probe) another conversation.
        // Generic 403 — never reveal whether the parent exists.
        if (! empty($options['parent_id'])) {
            $parent = DirectMessage::find((int) $options['parent_id']);
            abort_unless($parent, 403, 'You are not permitted to reply to this conversation.');
            $inThread = ((int) $parent->sender_id === (int) $sender->id && (int) $parent->recipient_id === (int) $recipient->id)
                || ((int) $parent->sender_id === (int) $recipient->id && (int) $parent->recipient_id === (int) $sender->id);
            abort_unless($inThread, 403, 'You are not permitted to reply to this conversation.');
            if ($sender->isCustomer() && ($parent->is_internal || ! $parent->is_customer_visible)) {
                abort(403, 'You are not permitted to reply to this conversation.');
            }
        }

        return DB::transaction(function () use ($sender, $recipient, $body, $subject, $options) {
            $internal = (bool) ($options['is_internal'] ?? false);
            // Customers can never create internal notes nor see them.
            if ($sender->isCustomer()) {
                $internal = false;
            }
            $message = DirectMessage::create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'parent_id' => $options['parent_id'] ?? null,
                'subject' => $subject,
                'body' => $body,
                'is_internal' => $internal,
                'is_customer_visible' => $internal ? false : true,
            ]);
            AuditLog::log('message.sent', 'direct_messages', $message, "Message from {$sender->name} to {$recipient->name}.".($internal ? ' (internal)' : ''));
            ServiceTrackingService::notify((int) $recipient->id, 'message_received', "New message from {$sender->name}", (string) mb_substr($body, 0, 140));

            return $message->fresh();
        });
    }

    /** Thread between two users, filtered for the viewer's authorization. */
    public function thread(User $viewer, User $other)
    {
        $q = DirectMessage::where(function ($w) use ($viewer, $other) {
            $w->where(fn ($x) => $x->where('sender_id', $viewer->id)->where('recipient_id', $other->id))
              ->orWhere(fn ($x) => $x->where('sender_id', $other->id)->where('recipient_id', $viewer->id));
        })->visibleTo($viewer)->with(['sender', 'recipient'])->latest();

        return $q->paginate(25);
    }

    public function inbox(User $user)
    {
        return DirectMessage::where('recipient_id', $user->id)
            ->visibleTo($user)->with('sender')->latest()->paginate(20);
    }

    public function markRead(DirectMessage $message, User $viewer): DirectMessage
    {
        abort_unless((int) $message->recipient_id === (int) $viewer->id, 403);
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return $message->fresh();
    }
}
