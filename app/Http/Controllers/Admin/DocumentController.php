<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashMemo;
use App\Models\Project;
use App\Models\Receipt;
use App\Models\ServiceOrder;
use App\Services\ServiceTrackingService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Staff document downloads. Receipts/cash memos inherit the finance-tier
 * route group; order/service PDFs match their source page visibility.
 */
class DocumentController extends Controller
{
    public function orderPdf($id)
    {
        // Staff-boundary: any authenticated staff member may open the work-order
        // page, but document download requires an explicit staff check so a
        // route-group slip can never expose customer PDFs to non-staff.
        abort_unless(auth()->user()?->isStaff(), 403);
        $order = ServiceOrder::with(['customer', 'service', 'invoices', 'payments'])->findOrFail($id);

        return Pdf::loadView('documents.order', compact('order'))->setPaper('a4')->download('order-'.$order->order_number.'.pdf');
    }

    public function receiptPdf(Receipt $receipt)
    {
        abort_unless(auth()->user()?->isFinanceManager(), 403);
        $receipt->load(['payment', 'invoice', 'customer', 'order.service']);

        return Pdf::loadView('documents.receipt', compact('receipt'))->setPaper('a4')->download('receipt-'.$receipt->receipt_number.'.pdf');
    }

    public function cashMemoPdf(CashMemo $memo)
    {
        abort_unless(auth()->user()?->isFinanceManager(), 403);
        $memo->load(['payment.invoice', 'payment.customer', 'payment.serviceOrder.service']);

        return Pdf::loadView('documents.cash-memo', compact('memo'))->setPaper('a4')->download('cash-memo-'.$memo->cash_memo_number.'.pdf');
    }

    public function serviceReport(Project $project)
    {
        abort_unless(auth()->user()?->isStaff(), 403);
        $project->load(['customer', 'service', 'projectManager', 'milestones', 'tasks.assignee']);
        $progress = ServiceTrackingService::projectProgress($project);
        $timeline = ServiceTrackingService::serviceTimeline(null, $project->id);

        return Pdf::loadView('documents.service-report', compact('project', 'progress', 'timeline'))->setPaper('a4')->download('service-report-'.$project->project_number.'.pdf');
    }
}
