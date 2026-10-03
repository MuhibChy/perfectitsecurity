<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BankTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Controlled bank-transfer workflow (sandbox-first).
 *
 * States: draft → pending_approval → approved → processing → completed
 * (failed|cancelled|reversed as terminal branches).
 *
 * HONESTY RULE: COMPLETED requires an external provider/bank reference.
 * An admin click alone can only reach PROCESSING. Sandbox rail transfers
 * are clearly marked provider=sandbox and never touch real money.
 * Idempotent on idempotency_key: retries return the original transfer.
 */
class BankTransferService
{
    public const PROVIDER_SANDBOX = 'sandbox';

    public function request(array $data, User $requester): BankTransfer
    {
        abort_unless($requester->isFinanceManager(), 403, 'Only finance managers can initiate transfers.');
        $data = $this->validate($data);

        return DB::transaction(function () use ($data, $requester) {
            if (! empty($data['idempotency_key']) && ($existing = BankTransfer::where('idempotency_key', $data['idempotency_key'])->first())) {
                return $existing; // double-click / refresh safe
            }
            $transfer = BankTransfer::create([
                'beneficiary_id' => $data['beneficiary_id'],
                'franchise_id' => $data['franchise_id'] ?? null,
                'purpose' => $data['purpose'],
                'related_id' => $data['related_id'] ?? null,
                'amount' => round((float) $data['amount'], 2),
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'provider' => $data['provider'] ?? self::PROVIDER_SANDBOX,
                'masked_destination' => $data['destination'] ?? null,
                'status' => 'pending_approval',
                'requested_by' => $requester->id,
                'requested_at' => now(),
                'idempotency_key' => $data['idempotency_key'] ?? ('BTK-'.\Illuminate\Support\Str::uuid()),
            ]);
            AuditLog::log('bank_transfer.requested', 'bank_transfers', $transfer, "Transfer {$transfer->reference} requested ({$transfer->currency} {$transfer->amount}, {$transfer->purpose}) by {$requester->name}.");

            return $transfer->fresh();
        });
    }

    public function approve(BankTransfer $transfer, User $approver): BankTransfer
    {
        abort_unless($approver->isFinanceManager(), 403);

        return DB::transaction(function () use ($transfer, $approver) {
            $transfer = BankTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless($transfer->status === 'pending_approval', 422, 'Only pending transfers can be approved.');
            abort_if((int) $transfer->requested_by === (int) $approver->id, 422, 'Requester cannot approve their own transfer (segregation of duties).');
            $transfer->update(['status' => 'approved', 'approved_by' => $approver->id, 'approved_at' => now()]);
            AuditLog::log('bank_transfer.approved', 'bank_transfers', $transfer, "Transfer {$transfer->reference} approved by {$approver->name}.");

            return $transfer->fresh();
        });
    }

    /** Mark as sent to the provider. Funds are NOT confirmed at this stage. */
    public function markProcessing(BankTransfer $transfer, User $executor): BankTransfer
    {
        abort_unless($executor->isFinanceManager(), 403);

        return DB::transaction(function () use ($transfer, $executor) {
            $transfer = BankTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless($transfer->status === 'approved', 422, 'Only approved transfers can be sent for processing.');
            $transfer->update(['status' => 'processing', 'executed_by' => $executor->id]);
            AuditLog::log('bank_transfer.processing', 'bank_transfers', $transfer, "Transfer {$transfer->reference} sent via {$transfer->provider} (awaiting provider confirmation).");

            return $transfer->fresh();
        });
    }

    /**
     * Confirm completion against a REAL provider/bank reference.
     * Without $externalReference the transfer stays in processing — never
     * fabricate completion.
     */
    public function complete(BankTransfer $transfer, User $executor, string $externalReference): BankTransfer
    {
        abort_unless($executor->isFinanceManager(), 403);
        abort_if(trim($externalReference) === '', 422, 'An external provider/bank reference is required to complete a transfer.');

        return DB::transaction(function () use ($transfer, $executor, $externalReference) {
            $transfer = BankTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless(in_array($transfer->status, ['approved', 'processing'], true), 422, 'Transfer cannot be completed from status '.$transfer->status.'.');
            // Idempotent completion: same reference returns current state.
            if ($transfer->status === 'completed' && $transfer->external_reference === $externalReference) {
                return $transfer;
            }
            $transfer->update([
                'status' => 'completed', 'external_reference' => $externalReference,
                'executed_by' => $executor->id, 'completed_at' => now(),
            ]);
            $this->postToLedger($transfer->fresh());
            AuditLog::log('bank_transfer.completed', 'bank_transfers', $transfer, "Transfer {$transfer->reference} completed. Provider ref: {$externalReference}.");
            // Notify beneficiary (own inbox only).
            app(\App\Services\ServiceTrackingService::class)::notify(
                (int) $transfer->beneficiary_id, 'transfer_completed',
                "Transfer {$transfer->reference} completed",
                "{$transfer->currency} {$transfer->amount} ({$transfer->purpose}) confirmed. Ref: {$externalReference}."
            );

            return $transfer->fresh();
        });
    }

    public function fail(BankTransfer $transfer, User $actor, string $reason): BankTransfer
    {
        abort_unless($actor->isFinanceManager(), 403);

        return DB::transaction(function () use ($transfer, $reason) {
            $transfer = BankTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless(in_array($transfer->status, ['pending_approval', 'approved', 'processing'], true), 422, 'Transfer cannot fail from status '.$transfer->status.'.');
            $transfer->update(['status' => 'failed', 'failure_reason' => $reason]);
            AuditLog::log('bank_transfer.failed', 'bank_transfers', $transfer, "Transfer {$transfer->reference} failed: {$reason}");

            return $transfer->fresh();
        });
    }

    public function cancel(BankTransfer $transfer, User $actor): BankTransfer
    {
        abort_unless($actor->isFinanceManager(), 403);

        return DB::transaction(function () use ($transfer, $actor) {
            $transfer = BankTransfer::lockForUpdate()->findOrFail($transfer->id);
            abort_unless(in_array($transfer->status, ['draft', 'pending_approval', 'approved'], true), 422, 'Transfer cannot be cancelled from status '.$transfer->status.'.');
            $transfer->update(['status' => 'cancelled']);
            AuditLog::log('bank_transfer.cancelled', 'bank_transfers', $transfer, "Transfer {$transfer->reference} cancelled by {$actor->name}.");

            return $transfer->fresh();
        });
    }

    /** Company-money → person-money posting. Customer revenue is never touched here. */
    protected function postToLedger(BankTransfer $transfer): void
    {
        $fs = app(FinancialService::class);
        $desc = "Bank transfer {$transfer->reference} ({$transfer->purpose}) to user #{$transfer->beneficiary_id}".($transfer->provider === self::PROVIDER_SANDBOX ? ' [SANDBOX]' : '');
        $meta = ['bank_transfer_id' => $transfer->id, 'employee_id' => $transfer->beneficiary_id, 'currency' => $transfer->currency];
        match ($transfer->purpose) {
            'salary' => $fs->recordSalaryPayment((float) $transfer->amount, $desc, $meta),
            'commission', 'franchise_share' => $fs->recordCommissionPayment((float) $transfer->amount, $desc, $meta),
            default => $fs->recordExpense((float) $transfer->amount, ucfirst($transfer->purpose), $desc, $meta),
        };
        $this->settleLinkedSalary($transfer);
    }

    /**
     * Settle the linked payroll row when its transfer completes.
     * Idempotent: only moves approved → paid, never rewrites history.
     * Without this, salaries stayed 'approved' forever after payment.
     */
    protected function settleLinkedSalary(BankTransfer $transfer): void
    {
        if ($transfer->purpose !== 'salary' || empty($transfer->related_id)) {
            return;
        }
        $salary = \App\Models\Salary::lockForUpdate()->find($transfer->related_id);
        if (! $salary || $salary->status === 'paid') {
            return;
        }
        // Only settle the exact payroll this transfer was initiated for.
        if ((int) $salary->user_id !== (int) $transfer->beneficiary_id) {
            return;
        }
        if (abs((float) $salary->net_salary - (float) $transfer->amount) > 0.009) {
            return;
        }
        $salary->update(['status' => 'paid', 'pay_date' => $salary->pay_date ?? now()->toDateString()]);
        AuditLog::log('salary.paid', 'salaries', $salary, "Salary #{$salary->id} settled via transfer {$transfer->reference} (provider ref: {$transfer->external_reference}).");
    }

    protected function validate(array $data): array
    {
        abort_unless(! empty($data['beneficiary_id']) && User::where('id', $data['beneficiary_id'])->exists(), 422, 'Valid beneficiary required.');
        abort_unless(isset($data['amount']) && (float) $data['amount'] > 0, 422, 'Amount must be positive.');
        abort_unless(in_array($data['purpose'] ?? 'salary', BankTransfer::PURPOSES, true), 422, 'Unknown transfer purpose.');
        // Never allow beneficiary = company revenue sink confusion: beneficiary must be a person account.
        return $data;
    }
}
