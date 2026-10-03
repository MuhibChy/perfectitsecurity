<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CashMemo;
use App\Models\Project;
use App\Models\Receipt;
use App\Models\ServiceOrder;
use App\Services\ServiceTrackingService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Customer document downloads. Every lookup is owner-scoped; cross-customer
 * IDs resolve to 403/404 without leaking.
 */
class DocumentCenterController extends Controller
{
    public function orderPdf($id)
    {
        $order = ServiceOrder::with(['customer', 'service', 'invoices', 'payments'])
            ->where('customer_id', auth()->id())->findOrFail($id);
        return Pdf::loadView('documents.order', compact('order'))->setPaper('a4')->download('order-' . $order->order_number . '.pdf');
    }

    public function receiptPdf($id)
    {
        $receipt = Receipt::with(['payment', 'invoice', 'customer', 'order.service'])
            ->where('customer_id', auth()->id())->findOrFail($id);
        return Pdf::loadView('documents.receipt', compact('receipt'))->setPaper('a4')->download('receipt-' . $receipt->receipt_number . '.pdf');
    }

    public function cashMemoPdf($id)
    {
        $memo = CashMemo::with(['payment.invoice', 'payment.customer'])
            ->whereHas('payment', fn ($q) => $q->where('customer_id', auth()->id()))->findOrFail($id);
        return Pdf::loadView('documents.cash-memo', compact('memo'))->setPaper('a4')->download('cash-memo-' . $memo->cash_memo_number . '.pdf');
    }

    public function serviceReport($id)
    {
        $project = Project::where('id', $id)->where('customer_id', auth()->id())->firstOrFail();
        $project->load(['customer', 'service', 'projectManager', 'milestones', 'tasks']);
        $progress = ServiceTrackingService::projectProgress($project);
        $timeline = ServiceTrackingService::serviceTimeline(null, $project->id, true);
        return Pdf::loadView('documents.service-report', compact('project', 'progress', 'timeline'))->setPaper('a4')->download('service-report-' . $project->project_number . '.pdf');
    }
}
