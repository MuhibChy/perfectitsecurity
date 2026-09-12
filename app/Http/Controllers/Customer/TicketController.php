<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('customer_id', auth()->id())
            ->with('category', 'assignee')
            ->latest()
            ->paginate(15);

        return view('customer.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = TicketCategory::where('is_active', true)->get();
        return view('customer.tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority' => 'required|in:low,medium,high,urgent,critical',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,txt,doc,docx,xls,xlsx|max:10240',
        ]);

        $ticket = Ticket::create([
            'customer_id' => auth()->id(),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'priority' => $validated['priority'],
            'severity' => 'moderate',
            'impact' => 'medium',
            'urgency' => 'medium',
            'status' => 'new',
        ]);

        app(SlaService::class)->applySla($ticket);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Ticket evidence is private customer data; it must never be served by /storage.
                $path = $file->store('ticket-attachments', 'local');
                $ticket->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('portal.tickets.show', $ticket)->with('success', 'Ticket created successfully!');
    }

    public function show($id)
    {
        $ticket = Ticket::where('customer_id', auth()->id())->with('messages.user', 'attachments', 'category', 'assignee')->findOrFail($id);
        return view('customer.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::where('customer_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,txt,doc,docx,xls,xlsx|max:10240',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        if ($ticket->status === 'waiting_customer') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->back()->with('success', 'Reply sent!');
    }

    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        abort_unless($ticket->customer_id === auth()->id() && $attachment->ticket_id === $ticket->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
