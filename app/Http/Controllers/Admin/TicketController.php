<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\TicketCategory;
use App\Models\SupportTeam;
use App\Services\SlaService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('customer', 'assignee', 'category', 'team');

        // Only the explicit support-agent role is scoped to its own queue.
        if ($request->user()->role === 'support_agent') {
            $query->where('assigned_to', $request->user()->id);
        }

        if ($request->status) $query->where('status', $request->status);
        if ($request->priority) $query->where('priority', $request->priority);
        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->assigned_to && $request->user()->isSupportManager()) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        $tickets = $query->latest()->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = Ticket::with('customer', 'assignee', 'category', 'subcategory', 'team', 'messages.user', 'attachments', 'timeEntries.user')->findOrFail($id);
        $this->ensureTicketAccess($ticket, false);
        $slaStatus = app(SlaService::class)->getSlaStatus($ticket);
        $agents = User::where('role', 'support_agent')->get();
        $teams = SupportTeam::where('is_active', true)->get();
        $categories = TicketCategory::where('is_active', true)->get();

        return view('admin.tickets.show', compact('ticket', 'slaStatus', 'agents', 'teams', 'categories'));
    }

    public function assign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, true);
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'team_id' => 'nullable|exists:support_teams,id',
        ]);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'team_id' => $request->team_id ?? $ticket->team_id,
            'status' => 'assigned',
        ]);

        return redirect()->back()->with('success', 'Ticket assigned successfully!');
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, false);

        // Handle time entry addition
        if ($request->boolean('add_time_entry')) {
            $request->validate([
                'minutes' => 'required|integer|min:1|max:480',
                'time_description' => 'nullable|string|max:500',
            ]);

            \App\Models\TicketTimeEntry::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'minutes' => $request->minutes,
                'description' => $request->time_description ?? 'Time logged',
                'date' => now()->toDateString(),
            ]);

            return redirect()->back()->with('success', 'Time entry added!');
        }

        $request->validate(['message' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        app(SlaService::class)->recordFirstResponse($ticket);

        if ($ticket->status === 'new' || $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->back()->with('success', 'Reply sent!');
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, false);
        $request->validate(['status' => 'required|in:new,open,assigned,in_progress,waiting_customer,waiting_third_party,escalated,resolved,closed,cancelled']);

        $ticket->update(['status' => $request->status]);

        if (in_array($request->status, ['resolved'])) {
            app(SlaService::class)->recordResolution($ticket);
        }
        if ($request->status === 'closed') {
            $ticket->update(['closed_at' => now()]);
        }

        return redirect()->back()->with('success', 'Ticket status updated!');
    }

    public function addNote(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, false);
        $request->validate(['note' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->note,
            'is_internal_note' => true,
        ]);

        return redirect()->back()->with('success', 'Internal note added!');
    }

    public function updateTags(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, false);

        $request->validate([
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $ticket->update(['tags' => $request->tags ?? []]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'tags' => $ticket->tags]);
        }

        return redirect()->back()->with('success', 'Tags updated!');
    }

    public function merge(Request $request, $id)
    {
        $primaryTicket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($primaryTicket, true);

        $request->validate([
            'merge_ticket_id' => 'required|exists:tickets,id|not_in:' . $id,
            'reason' => 'nullable|string|max:500',
        ]);

        $secondaryTicket = Ticket::findOrFail($request->merge_ticket_id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($primaryTicket, $secondaryTicket, $request) {
            // Move all messages from secondary to primary
            TicketMessage::where('ticket_id', $secondaryTicket->id)
                ->update(['ticket_id' => $primaryTicket->id]);

            // Move attachments
            \App\Models\TicketAttachment::where('ticket_id', $secondaryTicket->id)
                ->update(['ticket_id' => $primaryTicket->id]);

            // Move time entries
            \App\Models\TicketTimeEntry::where('ticket_id', $secondaryTicket->id)
                ->update(['ticket_id' => $primaryTicket->id]);

            // Add merge note
            $reason = $request->reason ? " (Reason: {$request->reason})" : '';
            TicketMessage::create([
                'ticket_id' => $primaryTicket->id,
                'user_id' => auth()->id(),
                'message' => "Merged ticket {$secondaryTicket->ticket_number} into this ticket{$reason}",
                'is_internal_note' => true,
            ]);

            // Soft delete secondary ticket
            $secondaryTicket->update([
                'status' => 'cancelled',
                'description' => $secondaryTicket->description . "\n\n--- MERGED INTO: {$primaryTicket->ticket_number} ---",
            ]);
            $secondaryTicket->delete();
        });

        return redirect()->route('admin.tickets.show', $primaryTicket)
            ->with('success', "Ticket {$secondaryTicket->ticket_number} has been merged into {$primaryTicket->ticket_number}.");
    }

    public function reopen(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ensureTicketAccess($ticket, false);

        $request->validate(['reason' => 'required|string|max:500']);

        $ticket->update([
            'status' => 'reopened',
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => "Ticket reopened. Reason: {$request->reason}",
        ]);

        return redirect()->back()->with('success', 'Ticket reopened!');
    }

    private function ensureTicketAccess(Ticket $ticket, bool $managerOnly): void
    {
        $user = request()->user();
        if ($user->isSupportManager() || $user->isAdmin()) {
            return;
        }

        abort_if($managerOnly || !$user->isSupportAgent() || (int) $ticket->assigned_to !== (int) $user->id, 403);
    }
}
