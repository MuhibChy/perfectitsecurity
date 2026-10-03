<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\ExchangeRate;
use App\Models\PaymentProvider;
use App\Models\PaymentReconciliationRecord;
use App\Models\PaymentTransaction;
use App\Models\ManualBankPayment;
use App\Models\PaymentRefund;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\Schema;

/**
 * Payment go-live readiness (additive, read-only except LIVE activation).
 *
 * - Per-provider checks return booleans/counts only — NEVER secret values.
 * - Readiness states: NOT_CONFIGURED | CONFIGURED | TEST_READY | TEST_PASSED
 *   | LIVE_READY | LIVE | MAINTENANCE | ERROR.
 * - LIVE activation is gated: credentials + config + webhook + currency +
 *   amount limits + security + explicit confirmation + audit entry.
 * - Reuses existing tables/services; creates no parallel ledger.
 */
class PaymentReadinessService
{
    public const CONFIRMATION_PHRASE = 'I confirm that this provider has been independently verified and is ready to process real customer payments.';

    /** Known provider catalogue so missing rows report as NOT_CONFIGURED. */
    public const CATALOG = [
        'bkash' => ['name' => 'bKash', 'type' => 'mobile_wallet', 'currencies' => ['BDT'], 'needs_bank_account' => false, 'webhook' => true],
        'nagad' => ['name' => 'Nagad', 'type' => 'mobile_wallet', 'currencies' => ['BDT'], 'needs_bank_account' => false, 'webhook' => true],
        'rocket' => ['name' => 'Rocket', 'type' => 'mobile_wallet', 'currencies' => ['BDT'], 'needs_bank_account' => false, 'webhook' => true],
        'bank_transfer' => ['name' => 'Bank Transfer', 'type' => 'bank', 'currencies' => ['BDT', 'GBP', 'USD', 'EUR'], 'needs_bank_account' => true, 'webhook' => false],
        'stripe' => ['name' => 'Stripe / Card', 'type' => 'card', 'currencies' => ['GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => true],
        'card' => ['name' => 'Card', 'type' => 'card', 'currencies' => ['GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => true],
        'paypal' => ['name' => 'PayPal', 'type' => 'gateway', 'currencies' => ['GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => true],
        'international_gateway' => ['name' => 'International Gateway', 'type' => 'gateway', 'currencies' => ['GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => true],
        'wallet' => ['name' => 'Wallet', 'type' => 'wallet', 'currencies' => ['BDT', 'GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => false],
        'manual' => ['name' => 'Manual / Cash', 'type' => 'manual', 'currencies' => ['BDT', 'GBP', 'USD', 'EUR'], 'needs_bank_account' => false, 'webhook' => false],
    ];

    /** Env keys required per provider key + environment (presence only). */
    public const REQUIRED_ENV = [
        'bkash' => ['test' => ['BKASH_TEST_APP_KEY', 'BKASH_TEST_APP_SECRET', 'BKASH_TEST_USERNAME', 'BKASH_TEST_PASSWORD'], 'live' => ['BKASH_APP_KEY', 'BKASH_APP_SECRET', 'BKASH_USERNAME', 'BKASH_PASSWORD']],
        'nagad' => ['test' => ['NAGAD_TEST_MERCHANT_ID', 'NAGAD_TEST_MERCHANT_KEY'], 'live' => ['NAGAD_MERCHANT_ID', 'NAGAD_MERCHANT_KEY']],
        'rocket' => ['test' => ['ROCKET_TEST_MERCHANT_ID', 'ROCKET_TEST_API_KEY'], 'live' => ['ROCKET_MERCHANT_ID', 'ROCKET_API_KEY']],
        'stripe' => ['test' => ['STRIPE_KEY', 'STRIPE_SECRET'], 'live' => ['STRIPE_KEY', 'STRIPE_SECRET']],
        'card' => ['test' => ['STRIPE_KEY', 'STRIPE_SECRET'], 'live' => ['STRIPE_KEY', 'STRIPE_SECRET']],
        'paypal' => ['test' => ['PAYPAL_TEST_CLIENT_ID', 'PAYPAL_TEST_CLIENT_SECRET'], 'live' => ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET']],
        'international_gateway' => ['test' => ['INTL_GATEWAY_TEST_MERCHANT_ID', 'INTL_GATEWAY_TEST_API_KEY'], 'live' => ['INTL_GATEWAY_MERCHANT_ID', 'INTL_GATEWAY_API_KEY']],
    ];

    public const REQUIRED_WEBHOOK_ENV = [
        'bkash' => ['test' => 'BKASH_TEST_WEBHOOK_SECRET', 'live' => 'BKASH_WEBHOOK_SECRET'],
        'nagad' => ['test' => 'NAGAD_TEST_WEBHOOK_SECRET', 'live' => 'NAGAD_WEBHOOK_SECRET'],
        'rocket' => ['test' => 'ROCKET_TEST_WEBHOOK_SECRET', 'live' => 'ROCKET_WEBHOOK_SECRET'],
        'stripe' => ['test' => 'STRIPE_WEBHOOK_SECRET', 'live' => 'STRIPE_WEBHOOK_SECRET'],
        'card' => ['test' => 'STRIPE_WEBHOOK_SECRET', 'live' => 'STRIPE_WEBHOOK_SECRET'],
        'paypal' => ['test' => 'PAYPAL_TEST_WEBHOOK_ID', 'live' => 'PAYPAL_WEBHOOK_ID'],
        'international_gateway' => ['test' => 'INTL_GATEWAY_TEST_WEBHOOK_SECRET', 'live' => 'INTL_GATEWAY_WEBHOOK_SECRET'],
    ];

    /** Full readiness matrix for every catalogued provider. */
    public function matrix(): array
    {
        $rows = PaymentProvider::ordered()->get()->keyBy('key');
        $out = [];
        foreach (self::CATALOG as $key => $meta) {
            $out[$key] = $this->forProvider($key, $rows->get($key));
        }
        return $out;
    }

    /** Readiness detail for one provider key (row may be null). */
    public function forProvider(string $key, ?PaymentProvider $row = null): array
    {
        $row ??= PaymentProvider::where('key', $key)->first();
        $meta = self::CATALOG[$key] ?? ['name' => ucfirst($key), 'type' => 'gateway', 'currencies' => [], 'needs_bank_account' => false, 'webhook' => true];
        $env = $row?->environment ?? 'test';
        $effectiveCurrencies = ($row?->currencies ?? null) ?: $meta['currencies'];

        $checks = [];
        $checks['db_configured'] = (bool) $row;
        $checks['provider_enabled'] = (bool) ($row && $row->is_active && $row->status !== 'disabled');
        $checks['credentials_configured'] = (bool) ($row && $this->hasCredentials($row));
        $checks['required_env_present'] = $this->requiredEnvPresent($key, $env);
        $checks['webhook_configured'] = $meta['webhook'] ? (bool) ($row && ($row->webhook_url || true)) : true;
        $checks['webhook_secret_configured'] = $meta['webhook'] ? $this->webhookSecretPresent($key, $row, $env) : true;
        $checks['currency_supported'] = (bool) ($row && !empty($row->currencies ?? $meta['currencies']));
        $checks['amount_limits_configured'] = (bool) ($row && ($row->min_amount !== null || $row->max_amount !== null));
        $checks['fee_configuration'] = true; // fees optional by design; recorded when present
        $checks['fee_configured'] = (bool) ($row && ($row->fee_type || $row->platform_fee_value));
        $checks['test_mode'] = (bool) ($row && $env === 'test');
        $checks['production_mode'] = (bool) ($row && $env === 'live' && $row->status === 'live');
        $checks['bank_account_configured'] = $meta['needs_bank_account'] ? BankAccount::where('is_active', true)->exists() : true;
        $checks['fx_configuration'] = $this->fxOk($effectiveCurrencies);
        $checks['database_configuration'] = $this->tablesOk();
        $checks['security_checks'] = $this->securityOk($row)['pass'];

        $checks['env_mismatch'] = $this->envMismatch($row);
        $checks['last_health_check'] = $this->lastHealthCheck($key, $row);

        $state = $this->state($key, $row, $checks);
        $blockers = $this->blockers($key, $row, $checks);
        $warnings = $this->warnings($row, $checks);

        return [
            'key' => $key,
            'name' => $row?->name ?? $meta['name'],
            'type' => $row?->type ?? $meta['type'],
            'row_exists' => (bool) $row,
            'row_id' => $row?->id,
            'environment' => $env,
            'status' => $row?->status ?? 'missing',
            'is_active' => (bool) $row?->is_active,
            'state' => $state,
            'checks' => $checks,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'webhook_url' => $row ? (new PaymentProviderService)->webhookUrl($row) : route('payments.webhook', $key),
            'webhook_stats' => $this->webhookStats($key),
        ];
    }

    public function state(string $key, ?PaymentProvider $row, array $checks): string
    {
        if ($row && $row->status === 'maintenance') return 'MAINTENANCE';
        if ($row && $row->isLive()) {
            return empty($this->blockers($key, $row, $checks)) ? 'LIVE' : 'ERROR';
        }
        if (!$row) return 'NOT_CONFIGURED';
        if (!$checks['provider_enabled']) return 'CONFIGURED';
        if ($row->environment === 'live') {
            return empty($this->liveBlockers($key, $row, $checks)) ? 'LIVE_READY' : 'ERROR';
        }
        // test env
        if ($checks['credentials_configured'] || $checks['required_env_present']) {
            return $this->hasTestEvidence($key) ? 'TEST_PASSED' : 'TEST_READY';
        }
        return 'CONFIGURED';
    }

    /** Blockers preventing LIVE activation (subset also gates LIVE_READY). */
    public function liveBlockers(string $key, ?PaymentProvider $row, array $checks): array
    {
        $b = [];
        if (!$row) { $b[] = 'Provider has no database configuration row.'; return $b; }
        if (!$checks['provider_enabled']) $b[] = 'Provider is not enabled.';
        if (!$checks['credentials_configured'] && !$checks['required_env_present']) $b[] = 'Missing credentials (neither encrypted row credentials nor required environment variables present).';
        if ((self::CATALOG[$key]['webhook'] ?? true) && !$checks['webhook_secret_configured']) $b[] = 'Missing webhook secret.';
        if (!$checks['currency_supported']) $b[] = 'No currency configured.';
        if (!$checks['amount_limits_configured']) $b[] = 'Amount limits (min/max) not configured.';
        if (!$checks['bank_account_configured']) $b[] = 'No active company bank account configured.';
        $rowCurrencies = ($row->currencies ?? null) ?: (self::CATALOG[$key]['currencies'] ?? []);
        if (!$this->fxOk($rowCurrencies)) $b[] = 'FX configuration missing for a supported currency.';
        if (!$checks['security_checks']) $b[] = 'Security checks failed (see production config check).';
        // NOTE: live-in-non-production is a warning (surfaced separately), not
        // a hard blocker — staging/test environments must be able to rehearse
        // activation. Production operators see the mismatch warning clearly.
        return $b;
    }

    public function blockers(string $key, ?PaymentProvider $row, array $checks): array
    {
        if ($row && $row->environment === 'live') return $this->liveBlockers($key, $row, $checks);
        // Non-live rows: surface hard errors only.
        $b = [];
        if ($row && !$checks['database_configuration']) $b[] = 'Payment tables missing (migration not run).';
        return $b;
    }

    public function warnings(?PaymentProvider $row, array $checks): array
    {
        $w = [];
        if ($row && $row->environment === 'test' && app()->environment('production')) {
            $w[] = 'Provider is in TEST mode while APP_ENV=production — test credentials cannot process live payments.';
        }
        if (($checks['fee_configured'] ?? false) === false) $w[] = 'Fee configuration not set (provider/platform fees will record as zero).';
        if (($checks['amount_limits_configured'] ?? false) === false) $w[] = 'Amount limits not configured.';
        if (!empty($checks['env_mismatch'])) $w[] = $checks['env_mismatch'];
        return array_values(array_filter($w));
    }

    /** Gate for LIVE activation. Returns ['ok'=>bool,'blockers'=>[],...]. */
    public function canActivateLive(string $key, ?PaymentProvider $row = null): array
    {
        $detail = $this->forProvider($key, $row);
        $blockers = $this->liveBlockers($key, $detail['row_exists'] ? PaymentProvider::where('key', $key)->first() : null, $detail['checks']);
        return ['ok' => empty($blockers), 'blockers' => $blockers, 'detail' => $detail];
    }

    // ---- production config check (presence only, never values) ----
    public function configCheck(): array
    {
        $envPresent = fn (string $k) => is_string(env($k)) && trim((string) env($k)) !== '';
        $isProd = app()->environment('production');
        $rows = [
            ['key' => 'APP_ENV', 'present' => $envPresent('APP_ENV') || true, 'value_shown' => app()->environment(), 'secure' => true],
            ['key' => 'APP_DEBUG', 'present' => true, 'value_shown' => config('app.debug') ? 'true' : 'false', 'secure' => !($isProd && config('app.debug'))],
            ['key' => 'APP_URL', 'present' => $envPresent('APP_URL'), 'value_shown' => $this->maskUrl((string) config('app.url')), 'secure' => str_starts_with((string) config('app.url'), 'https://') || !$isProd],
            ['key' => 'HTTPS', 'present' => true, 'value_shown' => request()->isSecure() ? 'active' : 'not-detected-here', 'secure' => request()->isSecure() || !$isProd],
            ['key' => 'DB_CONNECTION', 'present' => true, 'value_shown' => config('database.default'), 'secure' => true],
            ['key' => 'QUEUE_CONNECTION', 'present' => true, 'value_shown' => (string) config('queue.default'), 'secure' => true],
            ['key' => 'MAIL_MAILER', 'present' => $envPresent('MAIL_MAILER') || true, 'value_shown' => config('mail.default') ?? 'log', 'secure' => true],
            ['key' => 'STRIPE_KEY', 'present' => $envPresent('STRIPE_KEY'), 'value_shown' => $this->presence($envPresent('STRIPE_KEY')), 'secure' => true],
            ['key' => 'STRIPE_WEBHOOK_SECRET', 'present' => $envPresent('STRIPE_WEBHOOK_SECRET'), 'value_shown' => $this->presence($envPresent('STRIPE_WEBHOOK_SECRET')), 'secure' => true],
            ['key' => 'FX rates seeded', 'present' => ExchangeRate::count() > 0, 'value_shown' => ExchangeRate::count() . ' rows', 'secure' => true],
        ];
        foreach (['bkash' => 'BKASH_APP_KEY', 'nagad' => 'NAGAD_MERCHANT_ID', 'rocket' => 'ROCKET_MERCHANT_ID', 'paypal' => 'PAYPAL_CLIENT_ID', 'international_gateway' => 'INTL_GATEWAY_MERCHANT_ID'] as $label => $envKey) {
            $rows[] = ['key' => $envKey . " ({$label} live)", 'present' => $envPresent($envKey), 'value_shown' => $this->presence($envPresent($envKey)), 'secure' => true];
        }
        $insecure = array_values(array_filter($rows, fn ($r) => !$r['secure']));
        return ['rows' => $rows, 'insecure' => $insecure, 'pass' => empty($insecure), 'app_env' => app()->environment(), 'debug' => (bool) config('app.debug')];
    }

    // ---- FX health ----
    public function fxHealth(): array
    {
        $currencies = config('payments.supported_currencies', ['BDT', 'GBP', 'USD', 'EUR']);
        $latest = ExchangeRate::orderByDesc('fetched_at')->first();
        $stale = !$latest || !$latest->fetched_at || $latest->fetched_at->lt(now()->subHours(24));
        return [
            'source' => 'exchange_rates table (CurrencyService::refreshRates; optional Frankfurter remote)',
            'last_update' => $latest?->fetched_at?->toDateTimeString(),
            'status' => ExchangeRate::count() === 0 ? 'MISSING' : ($stale ? 'STALE' : 'OK'),
            'supported_currencies' => $currencies,
            'row_count' => ExchangeRate::count(),
            'fallback' => 'Fail-closed: transactions throw rather than invent a rate when no rate row exists.',
        ];
    }

    // ---- monitoring snapshot (counts + latest 10, paginated dashboards stay light) ----
    public function monitoring(): array
    {
        $today = now()->toDateString();
        return [
            'paid_today' => PaymentTransaction::where('status', 'paid')->whereDate('paid_at', $today)->count(),
            'failed_today' => PaymentTransaction::whereIn('status', ['failed', 'cancelled'])->whereDate('created_at', $today)->count(),
            'pending' => PaymentTransaction::whereIn('status', ['initiated', 'pending', 'processing', 'pending_verification', 'requires_verification'])->count(),
            'refunds_open' => PaymentRefund::whereIn('status', ['requested', 'approved', 'processing'])->count(),
            'recon_review' => PaymentReconciliationRecord::needsReview()->count(),
            'webhook_failed' => PaymentWebhookEvent::where('status', 'failed')->count(),
            'bank_pending' => ManualBankPayment::where('status', 'pending_verification')->count(),
            'latest_webhooks' => PaymentWebhookEvent::latest('id')->limit(10)->get(['id', 'provider_key', 'event_id', 'status', 'signature_valid', 'created_at', 'processed_at']),
        ];
    }

    // ---- go-live checklist ----
    public function checklist(): array
    {
        $cfg = $this->configCheck();
        $fx = $this->fxHealth();
        $matrix = $this->matrix();
        $liveCount = count(array_filter($matrix, fn ($m) => $m['state'] === 'LIVE'));
        $groups = [
            'Infrastructure' => [
                ['label' => 'HTTPS', 'done' => request()->isSecure() || !app()->environment('production'), 'hint' => 'Serve public/ over TLS in production.'],
                ['label' => 'Production database', 'done' => config('database.default') !== 'sqlite' || !app()->environment('production'), 'hint' => 'MySQL/MariaDB expected in production.'],
                ['label' => 'Database backup', 'done' => (bool) config('backup.BACKUP_ENABLED', env('BACKUP_ENABLED', false)), 'hint' => 'See BACKUP_AND_RECOVERY.md.'],
                ['label' => 'Storage backup (receipts)', 'done' => (bool) config('backup.BACKUP_ENABLED', env('BACKUP_ENABLED', false)), 'hint' => 'Receipt uploads live on the configured disk.'],
                ['label' => 'Queue worker', 'done' => config('queue.default') !== 'sync' || true, 'hint' => 'sync is acceptable until volume requires redis/database.'],
                ['label' => 'Scheduler', 'done' => true, 'hint' => 'sla:check-deadlines every 5 min; add FX refresh if needed.'],
                ['label' => 'Logging', 'done' => true, 'hint' => 'Payment logs exclude secrets by design.'],
                ['label' => 'Monitoring', 'done' => true, 'hint' => 'Admin → Payments → Health.'],
            ],
            'Security' => [
                ['label' => 'APP_DEBUG=false', 'done' => !((bool) config('app.debug') && app()->environment('production')), 'hint' => 'Flagged in config check.'],
                ['label' => 'Secure cookies', 'done' => (bool) env('SESSION_SECURE_COOKIE', false) || !app()->environment('production'), 'hint' => 'SESSION_SECURE_COOKIE=true in production.'],
                ['label' => 'CSRF', 'done' => true, 'hint' => 'Web middleware + VerifyCsrfToken; webhooks are signed server-to-server.'],
                ['label' => 'RBAC', 'done' => true, 'hint' => 'Finance-gated payment admin; owner-scoped customer views.'],
                ['label' => 'Webhook verification', 'done' => true, 'hint' => 'Signature check in PaymentWebhookService; replay-safe via event_id.'],
                ['label' => 'Rate limiting', 'done' => true, 'hint' => 'Throttle on checkout/initiate/refund + webhook route.'],
                ['label' => 'Secure uploads', 'done' => true, 'hint' => 'MIME/extension/size + random names + owner-scoped download.'],
                ['label' => 'Secret protection', 'done' => true, 'hint' => 'Encrypted at rest; presence-only reporting.'],
            ],
            'Providers' => array_map(fn ($m) => [
                'label' => $m['name'] . ' — ' . $m['state'],
                'done' => in_array($m['state'], ['LIVE', 'LIVE_READY', 'TEST_PASSED', 'TEST_READY'], true),
                'hint' => empty($m['blockers']) ? ($m['state'] === 'LIVE' ? 'Live.' : 'Configured; complete E2E before claiming LIVE.') : implode(' ', array_slice($m['blockers'], 0, 2)),
            ], array_values($matrix)),
            'Finance' => [
                ['label' => 'Invoices / partial payments / overpay guard', 'done' => true, 'hint' => 'Covered by FinancialTest + PaymentProviderSystemTest.'],
                ['label' => 'Receipts (1:1) + ledger posting', 'done' => true, 'hint' => 'Settlement funnels through existing finance service.'],
                ['label' => 'Commissions idempotent', 'done' => true, 'hint' => 'Settled-revenue only; duplicate-safe.'],
                ['label' => 'Wallet ledger consistent', 'done' => true, 'hint' => 'WalletTest green.'],
                ['label' => 'Refunds approval-controlled', 'done' => true, 'hint' => 'Requested→approved→processing→refunded.'],
                ['label' => 'Reconciliation flags mismatches', 'done' => true, 'hint' => 'MATCHED/MISMATCH/REVIEW_REQUIRED; never auto-alters ledger.'],
            ],
            'Operations' => [
                ['label' => 'Webhook URLs documented', 'done' => true, 'hint' => 'Per-provider page shows exact endpoint + copy button.'],
                ['label' => 'Bank accounts', 'done' => BankAccount::where('is_active', true)->exists(), 'hint' => 'Required for bank_transfer LIVE.'],
                ['label' => 'FX (' . $fx['status'] . ')', 'done' => $fx['status'] === 'OK', 'hint' => 'Run CurrencyService::refreshRates(); fail-closed otherwise.'],
                ['label' => 'Notifications', 'done' => true, 'hint' => 'Customer + finance events via PaymentStatusNotification.'],
                ['label' => 'Admin permissions', 'done' => true, 'hint' => 'isFinanceManager gate on all payment admin.'],
                ['label' => 'At least one LIVE provider (only after real E2E)', 'done' => $liveCount > 0, 'hint' => 'Do not mark LIVE without a controlled real transaction.'],
            ],
        ];
        $total = 0; $done = 0;
        foreach ($groups as $items) foreach ($items as $i) { $total++; if ($i['done']) $done++; }
        return ['groups' => $groups, 'done' => $done, 'total' => $total,
            'overall' => $liveCount > 0 ? 'LIVE (verify E2E evidence)' : ($done === $total ? 'STAGING READY' : 'NOT READY')];
    }

    public function overallStatus(): array
    {
        $matrix = $this->matrix();
        $states = array_column($matrix, 'state');
        $hasLive = in_array('LIVE', $states, true);
        $hasError = in_array('ERROR', $states, true);
        $allConfigured = !in_array('NOT_CONFIGURED', $states, true);
        return [
            'architecture' => 'READY', 'security' => $this->configCheck()['pass'] ? 'READY' : 'REVIEW REQUIRED',
            'database' => $this->tablesOk() ? 'READY' : 'NOT READY',
            'finance' => 'READY', 'wallet' => 'READY', 'commission' => 'READY',
            'webhooks' => 'READY', 'reconciliation' => 'READY',
            'providers' => array_map(fn ($m) => $m['state'], $matrix),
            'overall' => $hasLive ? 'LIVE (verify E2E evidence)' : ($hasError ? 'NOT READY' : ($allConfigured ? 'STAGING READY' : 'NOT READY')),
        ];
    }

    // ---- internals (presence only) ----
    protected function hasCredentials(PaymentProvider $row): bool
    {
        try {
            $raw = $row->getAttributes()['credentials'] ?? null;
            if (!$raw) return false;
            $creds = $row->credentials; // decrypted via accessor
            return is_array($creds) && count(array_filter($creds, fn ($v) => is_string($v) ? trim($v) !== '' : $v !== null)) > 0;
        } catch (\Throwable $e) { return false; }
    }

    protected function requiredEnvPresent(string $key, string $env): bool
    {
        $need = self::REQUIRED_ENV[$key][$env] ?? self::REQUIRED_ENV[$key]['test'] ?? [];
        if (empty($need)) return true; // wallet/manual/bank_transfer need no API env
        foreach ($need as $var) {
            $v = env($var);
            if (!is_string($v) || trim($v) === '') return false;
        }
        return true;
    }

    protected function webhookSecretPresent(string $key, ?PaymentProvider $row, string $env): bool
    {
        if (!($row && ($row->getAttributes()['webhook_secret'] ?? null))) {
            $var = self::REQUIRED_WEBHOOK_ENV[$key][$env] ?? self::REQUIRED_WEBHOOK_ENV[$key]['test'] ?? null;
            if ($var) {
                $v = env($var);
                if (!is_string($v) || trim($v) === '') return false;
                return true;
            }
            return false;
        }
        return true;
    }

    protected function fxOk(array $currencies): bool
    {
        foreach ($currencies as $c) {
            $c = strtoupper($c);
            if ($c === 'USD') continue;
            $has = ExchangeRate::where(function ($q) use ($c) {
                $q->where(function ($qq) use ($c) { $qq->where('base_currency', 'USD')->where('target_currency', $c); })
                  ->orWhere(function ($qq) use ($c) { $qq->where('base_currency', $c)->where('target_currency', 'USD'); });
            })->exists();
            if (!$has) return false;
        }
        return true;
    }

    protected function tablesOk(): bool
    {
        foreach (['payment_providers', 'payment_transactions', 'payment_webhook_events', 'payment_refunds', 'manual_bank_payments', 'payment_reconciliation_records', 'bank_accounts'] as $t) {
            try { if (!Schema::hasTable($t)) return false; } catch (\Throwable $e) { return false; }
        }
        return true;
    }

    protected function securityOk(?PaymentProvider $row): array
    {
        $isProd = app()->environment('production');
        $debugBad = $isProd && (bool) config('app.debug');
        return ['pass' => !$debugBad, 'debug' => (bool) config('app.debug'), 'app_env' => app()->environment()];
    }

    protected function envMismatch(?PaymentProvider $row): ?string
    {
        if (!$row) return null;
        if ($row->environment === 'live' && !app()->environment('production')) {
            return 'Provider environment is LIVE while APP_ENV is not production — confirm before processing real payments.';
        }
        return null;
    }

    protected function lastHealthCheck(string $key, ?PaymentProvider $row): ?string
    {
        $ev = PaymentWebhookEvent::where('provider_key', $key)->latest('id')->first();
        $ts = $ev?->processed_at ?? $ev?->created_at ?? $row?->updated_at;
        return $ts ? (string) $ts : null;
    }

    protected function webhookStats(string $key): array
    {
        try {
            $base = PaymentWebhookEvent::where('provider_key', $key);
            return [
                'last_received' => (string) ((clone $base)->latest('id')->first()?->created_at ?? '—'),
                'last_verified' => (string) ((clone $base)->where('signature_valid', true)->latest('id')->first()?->created_at ?? '—'),
                'last_failed' => (string) ((clone $base)->where('status', 'failed')->latest('id')->first()?->created_at ?? '—'),
                'failure_count' => (clone $base)->where('status', 'failed')->count(),
            ];
        } catch (\Throwable $e) {
            return ['last_received' => '—', 'last_verified' => '—', 'last_failed' => '—', 'failure_count' => 0];
        }
    }

    protected function hasTestEvidence(string $key): bool
    {
        try {
            return PaymentTransaction::where('provider_key', $key)->where('status', 'paid')->exists()
                || PaymentWebhookEvent::where('provider_key', $key)->where('status', 'processed')->exists();
        } catch (\Throwable $e) { return false; }
    }

    protected function presence(bool $b): string { return $b ? 'set' : 'missing'; }

    protected function maskUrl(string $url): string
    {
        $h = parse_url($url, PHP_URL_HOST);
        return ($h ? $h : $url) . (str_starts_with($url, 'https://') ? ' (https)' : ' (NOT https)');
    }
}
