<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DirectoryService;
use Illuminate\Http\Request;

/**
 * Permission-aware portal search (§21): customers search ONLY their own
 * records (by reference number) plus the support-team directory.
 */
class SearchController extends Controller
{
    public function index(Request $request, DirectoryService $directory)
    {
        $q = trim((string) $request->input('q', ''));
        $q = mb_substr($q, 0, 100);
        $results = ['staff' => [], 'references' => []];
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $me = auth()->user();
            $staff = User::where('is_active', true)->whereNotIn('role', ['customer'])
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->take(10)->get()->map(fn ($s) => $directory->publicCard($s));
            $results['staff'] = $staff;
            $refs = [];
            $find = function ($model, $column, $label, $route) use ($me, $like, &$refs) {
                foreach ($model::where('customer_id', $me->id)->where($column, 'like', $like)->take(5)->get() as $row) {
                    $refs[] = ['label' => $label, 'ref' => $row->$column, 'url' => route($route, $row)];
                }
            };
            $find(Ticket::class, 'ticket_number', 'Ticket', 'portal.tickets.show');
            $find(Invoice::class, 'invoice_number', 'Invoice', 'portal.invoices.show');
            $find(ServiceOrder::class, 'order_number', 'Order', 'portal.orders.show');
            $find(Project::class, 'project_number', 'Project', 'portal.projects.show');
            $results['references'] = $refs;
        }

        return view('customer.search.index', ['q' => $q, 'results' => $results]);
    }
}
