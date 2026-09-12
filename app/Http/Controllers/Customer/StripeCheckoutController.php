<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    public function checkoutInvoice(Request $request, $id, StripePaymentService $stripe)
    {
        $invoice = Invoice::where('customer_id', auth()->id())->findOrFail($id);

        if (!$stripe->isConfigured()) {
            return redirect()->back()->withErrors([
                'payment' => 'Online payments are not configured. Please contact support or pay offline.',
            ]);
        }

        try {
            $session = $stripe->createInvoiceCheckout(
                $invoice,
                route('portal.invoices.checkout.success', $invoice->id) . '?session_id={CHECKOUT_SESSION_ID}',
                route('portal.invoices.show', $invoice->id)
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['payment' => $e->getMessage()]);
        }

        if (!$session) {
            return redirect()->back()->withErrors(['payment' => 'Unable to start Stripe checkout.']);
        }

        return redirect()->away($session->url);
    }

    public function success($id)
    {
        $invoice = Invoice::where('customer_id', auth()->id())->findOrFail($id);

        return redirect()->route('portal.invoices.show', $invoice->id)
            ->with('success', 'Payment submitted. Confirmation may take a few moments while Stripe notifies us.');
    }
}
