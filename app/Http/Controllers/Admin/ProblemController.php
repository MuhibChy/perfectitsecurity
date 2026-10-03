<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ItsmService;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::with('customer', 'assignee');
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('problem_number', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            });
        }
        $problems = $query->latest()->paginate(20);

        return view('admin.problems.index', compact('problems'));
    }

    public function create()
    {
        $agents = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin'])
            ->where('is_active', true)->get();
        $tickets = Ticket::open()->latest()->limit(100)->get(['id', 'ticket_number', 'subject']);

        return view('admin.problems.create', compact('agents', 'tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_id' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
            'priority' => 'required|in:low,medium,high,urgent,critical',
            'impact' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'ticket_ids' => 'nullable|array',
            'ticket_ids.*' => 'exists:tickets,id',
        ]);
        $ticketIds = $data['ticket_ids'] ?? [];
        unset($data['ticket_ids']);

        $problem = Problem::create($data);
        if ($ticketIds) {
            $problem->tickets()->sync($ticketIds);
        }
        AuditService::log('problem.create', 'itsm', $problem, "Problem {$problem->problem_number} created");

        return redirect()->route('admin.problems.show', $problem)->with('success', 'Problem created.');
    }

    public function show(Problem $problem)
    {
        $problem->load('customer', 'assignee', 'tickets.customer');
        $agents = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin'])
            ->where('is_active', true)->get();
        $openTickets = Ticket::open()->latest()->limit(100)->get(['id', 'ticket_number', 'subject']);

        return view('admin.problems.show', compact('problem', 'agents', 'openTickets'));
    }

    public function update(Request $request, Problem $problem)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'sometimes|in:low,medium,high,urgent,critical',
            'impact' => 'sometimes|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);
        $old = $problem->only(array_keys($data));
        $problem->update($data);
        AuditService::log('problem.update', 'itsm', $problem, "Problem {$problem->problem_number} updated", $old, $data);

        return redirect()->back()->with('success', 'Problem updated.');
    }

    public function transition(Request $request, Problem $problem, ItsmService $itsm)
    {
        $data = $request->validate(['to' => 'required|in:open,investigating,known_error,resolved,closed']);
        $extra = $request->validate([
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);
        $itsm->transitionProblem($problem, $data['to'], $request->user()->id, array_filter($extra, fn ($v) => $v !== null));

        return redirect()->back()->with('success', "Problem moved to {$data['to']}.");
    }

    public function linkTicket(Request $request, Problem $problem)
    {
        $data = $request->validate(['ticket_id' => 'required|exists:tickets,id']);
        $problem->tickets()->syncWithoutDetaching([$data['ticket_id']]);
        AuditService::log('problem.link', 'itsm', $problem, "Ticket linked to {$problem->problem_number}");

        return redirect()->back()->with('success', 'Incident linked.');
    }

    public function unlinkTicket(Problem $problem, Ticket $ticket)
    {
        $problem->tickets()->detach($ticket->id);
        AuditService::log('problem.unlink', 'itsm', $problem, "Ticket {$ticket->ticket_number} unlinked");

        return redirect()->back()->with('success', 'Incident unlinked.');
    }
}
