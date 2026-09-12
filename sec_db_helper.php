<?php
/**
 * SEC ASSESSMENT TEST HELPER (synthetic data only — removed after testing).
 * Boots the app and exposes CLI subcommands for seeding/inspecting test state.
 * Usage: php sec_db_helper.php <command> [args...]
 */
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use App\Models\User;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$cmd = $argv[1] ?? 'state';

function testCustomerA() { return User::where('email', 'sec_test_cust_a@example.test')->first(); }
function testCustomerB() { return User::where('email', 'sec_test_cust_b@example.test')->first(); }

switch ($cmd) {
case 'seed':
    $a = testCustomerA();
    if (!$a) {
        $a = User::create([
            'name' => 'SEC Test Customer A',
            'email' => 'sec_test_cust_a@example.test',
            'password' => Hash::make('TestPass123!'),
            'role' => 'customer',
            'is_active' => true,
        ]);
        echo "Created customer A id={$a->id}\n";
    } else {
        echo "Customer A exists id={$a->id}\n";
    }
    $b = testCustomerB();
    if (!$b) {
        $b = User::create([
            'name' => 'SEC Test Customer B',
            'email' => 'sec_test_cust_b@example.test',
            'password' => Hash::make('TestPass123!'),
            'role' => 'customer',
            'is_active' => true,
        ]);
        echo "Created customer B id={$b->id}\n";
    } else {
        echo "Customer B exists id={$b->id}\n";
    }
    break;

case 'webhook-prep':
    // Create a synthetic order + invoice that would normally result from a
    // service order awaiting gateway payment.
    $a = testCustomerA();
    $order = ServiceOrder::create([
        'order_number' => 'SEC-' . strtoupper(Str::random(6)),
        'customer_id' => $a->id,
        'total' => 100.00,
        'amount_paid' => 0,
        'amount_due' => 100.00,
        'currency' => 'USD',
        'status' => 'awaiting_payment',
        'payment_authorization' => 'deposit_required',
        'source' => 'sec_test',
    ]);
    $invoice = Invoice::create([
        'customer_id' => $a->id,
        'service_order_id' => $order->id,
        'number' => 'SEC-INV-' . strtoupper(Str::random(6)),
        'status' => 'awaiting_payment',
        'currency' => 'USD',
        'subtotal' => 100.00,
        'total' => 100.00,
        'amount_paid' => 0,
        'amount_due' => 100.00,
        'tax_rate' => 0,
        'discount_amount' => 0,
        'discount_type' => 'fixed',
        'issued_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
    ]);
    $invoice->stripe_checkout_session_id = null;
    $invoice->save();
    echo "webhook target: order_id={$order->id} invoice_id={$invoice->id} status={$invoice->status}\n";
    break;

case 'webhook-reset':
    $invId = (int) ($argv[2] ?? 0);
    $invoice = Invoice::find($invId);
    if (!$invoice) { echo "invoice not found\n"; break; }
    Payment::where('invoice_id', $invId)->delete();
    $invoice->update([
        'status' => 'awaiting_payment',
        'amount_paid' => 0,
        'amount_due' => $invoice->total,
        'paid_at' => null,
        'stripe_checkout_session_id' => null,
    ]);
    $invoice->serviceOrder()->update(['amount_paid' => 0, 'amount_due' => $invoice->total, 'status' => 'awaiting_payment', 'payment_authorization' => 'deposit_required']);
    echo "reset invoice {$invId}\n";
    break;

case 'order-for':
    // order-for <customerEmail> <amount>
    $email = $argv[2];
    $amount = (float) ($argv[3] ?? 100.00);
    $u = User::where('email', $email)->first();
    $order = ServiceOrder::create([
        'order_number' => 'SEC-' . strtoupper(Str::random(6)),
        'customer_id' => $u->id,
        'total' => $amount,
        'amount_paid' => 0,
        'amount_due' => $amount,
        'currency' => 'USD',
        'status' => 'awaiting_payment',
        'payment_authorization' => 'deposit_required',
        'source' => 'sec_test',
    ]);
    $invoice = Invoice::create([
        'customer_id' => $u->id,
        'service_order_id' => $order->id,
        'number' => 'SEC-INV-' . strtoupper(Str::random(6)),
        'status' => 'awaiting_payment',
        'currency' => 'USD',
        'subtotal' => $amount,
        'total' => $amount,
        'amount_paid' => 0,
        'amount_due' => $amount,
        'tax_rate' => 0,
        'discount_amount' => 0,
        'discount_type' => 'fixed',
        'issued_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
    ]);
    echo "order_id={$order->id} invoice_id={$invoice->id} amount={$amount}\n";
    break;

case 'dump':
    // dump <orderId>
    $oid = (int) ($argv[2] ?? 0);
    $order = ServiceOrder::with('invoices', 'payments')->find($oid);
    if (!$order) { echo "order not found\n"; break; }
    echo json_encode([
        'order' => ['id' => $order->id, 'status' => $order->status, 'total' => $order->total, 'amount_paid' => $order->amount_paid, 'amount_due' => $order->amount_due, 'payment_authorization' => $order->payment_authorization],
        'invoices' => $order->invoices->map(fn($i) => ['id' => $i->id, 'status' => $i->status, 'amount_paid' => $i->amount_paid, 'amount_due' => $i->amount_due, 'total' => $i->total]),
        'payments' => $order->payments->map(fn($p) => ['id' => $p->id, 'amount' => $p->amount, 'status' => $p->status, 'method' => $p->payment_method, 'txn' => $p->transaction_id, 'stripe_session' => $p->stripe_checkout_session_id, 'paid_at' => $p->paid_at ? $p->paid_at->toDateTimeString() : null]),
    ], 0), "\n";
    break;

case 'state':
default:
    $users = User::where('email', 'like', 'sec_test_%')->get()->map(fn($u) => ['id' => $u->id, 'email' => $u->email]);
    echo "Test users: " . json_encode($users) . "\n";
    $orders = DB::select("SELECT o.id, o.status, o.total, o.amount_paid, o.amount_due FROM service_orders o WHERE o.source='sec_test' ORDER BY o.id");
    echo "Test orders: " . json_encode($orders) . "\n";
    $invs = DB::select("SELECT i.id, i.status, i.total, i.amount_paid, i.amount_due, i.stripe_checkout_session_id FROM invoices i JOIN service_orders o ON o.id = i.service_order_id WHERE o.source='sec_test' ORDER BY i.id");
    echo "Test invoices: " . json_encode($invs) . "\n";
    break;
}