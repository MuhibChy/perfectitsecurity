<?php
/**
 * SEC ASSESSMENT TEST HELPER (synthetic data only — removed after testing).
 * Uses raw PDO against the SQLite file. No framework boot.
 * Usage: php sec_db_helper.php <command> [args...]
 */
error_reporting(E_ERROR | E_PARSE);

$dbPath = 'C:/xampp/htdocs/IT Service freelace/techsupport-platform/database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cmd = $argv[1] ?? 'state';

function nowStr() { return date('Y-m-d H:i:s'); }
function todayStr() { return date('Y-m-d'); }
function esc($v) { return $pdo->quote((string) $v); }

function ensureService(): int
{
    $row = $pdo->query('SELECT id FROM services ORDER BY id LIMIT 1')->fetch();
    if ($row) return (int) $row['id'];
    $pdo->exec("INSERT INTO services (name, slug, short_description, description, is_active, is_demo) VALUES ('SEC Test Service','sec-test-service','synthetic','synthetic',1,0)");
    return (int) $pdo->lastInsertId();
}

function ensureCustomer(string $email, string $name): int
{
    $row = $pdo->prepare('SELECT id FROM users WHERE email = ?')->execute([$email])->fetch();
    if ($row) return (int) $row['id'];
    $hash = password_hash('TestPass123!', PASSWORD_BCRYPT);
    $pdo->prepare('INSERT INTO users (name, email, email_verified_at, password, role, is_active, two_factor_enabled, verification_status, is_demo, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$name, $email, nowStr(), $hash, 'customer', 1, 0, 'verified', 0, nowStr(), nowStr()]);
    return (int) $pdo->lastInsertId();
}

function createOrderAndInvoice(int $custId, float $amount): array
{
    $svc = ensureService();
    $orderNo = 'SEC-' . strtoupper(bin2hex(random_bytes(4)));
    $invNo   = 'SEC-INV-' . strtoupper(bin2hex(random_bytes(4)));
    $t = nowStr(); $d = todayStr(); $due = date('Y-m-d', time() + 14 * 86400);
    $pdo->prepare('INSERT INTO service_orders (order_number, customer_id, service_id, source, requirements, priority, status, payment_authorization, currency, original_price, final_price, discount_amount, tax_rate, tax_amount, total, amount_paid, amount_due, expected_cost, actual_cost, urgency, price_locked, is_demo, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$orderNo, $custId, $svc, 'sec_test', 'SYNTHETIC TEST DATA — NOT REAL', 'medium', 'awaiting_payment', 'deposit_required', 'USD', $amount, $amount, 0, 0, 0, $amount, 0, $amount, 0, 0, 'normal', 1, 0, $t, $t]);
    $oid = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO invoices (invoice_number, customer_id, subtotal, discount_amount, discount_type, tax_rate, tax_amount, total, amount_paid, amount_due, status, issued_date, due_date, service_order_id, currency, is_demo, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$invNo, $custId, $amount, 0, 'fixed', 0, 0, $amount, 0, $amount, 'awaiting_payment', $d, $due, $oid, 'USD', 0, $t, $t]);
    $iid = (int) $pdo->lastInsertId();
    return ['order_id' => $oid, 'invoice_id' => $iid, 'amount' => $amount];
}

switch ($cmd) {
case 'seed':
    $a = ensureCustomer('sec_test_cust_a@example.test', 'SEC Test Customer A');
    $b = ensureCustomer('sec_test_cust_b@example.test', 'SEC Test Customer B');
    echo "customer_a=$a customer_b=$b\n";
    break;

case 'webhook-prep':
    $a = (int) $argv[2];
    $r = createOrderAndInvoice($a, 100.00);
    echo json_encode($r), "\n";
    break;

case 'webhook-reset':
    $iid = (int) $argv[2];
    $pdo->prepare('DELETE FROM payments WHERE invoice_id = ?')->execute([$iid]);
    $pdo->prepare('UPDATE invoices SET status = ?, amount_paid = 0, amount_due = total, paid_at = NULL, stripe_checkout_session_id = NULL, stripe_payment_intent_id = NULL WHERE id = ?')->execute(['awaiting_payment', $iid]);
    $pdo->prepare('UPDATE service_orders SET status = ?, amount_paid = 0, amount_due = total, payment_authorization = ? WHERE id = (SELECT service_order_id FROM invoices WHERE id = ?)')->execute(['awaiting_payment', 'deposit_required', $iid]);
    echo "reset invoice $iid\n";
    break;

case 'order-for':
    $email = $argv[2];
    $amount = (float) ($argv[3] ?? 100.00);
    $u = $pdo->prepare('SELECT id FROM users WHERE email = ?')->execute([$email])->fetch();
    $r = createOrderAndInvoice((int) $u['id'], $amount);
    echo json_encode($r), "\n";
    break;

case 'dump':
    $oid = (int) ($argv[2] ?? 0);
    $o = $pdo->prepare('SELECT id, status, total, amount_paid, amount_due, payment_authorization FROM service_orders WHERE id = ?')->execute([$oid])->fetch();
    if (!$o) { echo "order not found\n"; break; }
    $inv = $pdo->prepare('SELECT id, status, total, amount_paid, amount_due, stripe_checkout_session_id AS session FROM invoices WHERE service_order_id = ?')->execute([$oid])->fetchAll();
    $pay = $pdo->prepare('SELECT id, amount, status, payment_method AS method, transaction_id AS txn, stripe_checkout_session_id AS session FROM payments WHERE service_order_id = ?')->execute([$oid])->fetchAll();
    echo json_encode(['order' => $o, 'invoices' => $inv, 'payments' => $pay], 0), "\n";
    break;

case 'state':
default:
    $users = $pdo->query("SELECT id, email FROM users WHERE email LIKE 'sec_test_%'")->fetchAll();
    echo "Test users: ", json_encode($users), "\n";
    $rows = $pdo->query("SELECT o.id AS order_id, o.status AS o_status, o.total, o.amount_paid, o.amount_due, i.id AS invoice_id, i.status AS i_status FROM service_orders o LEFT JOIN invoices i ON i.service_order_id = o.id WHERE o.source='sec_test' ORDER BY o.id")->fetchAll();
    echo "Test orders/invoices: ", json_encode($rows), "\n";
    break;
}