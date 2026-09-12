<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceOrder extends Model
{
    protected $fillable = ['is_demo', 'order_number', 'customer_id', 'service_id', 'created_by', 'assigned_to', 'source', 'order_source_label', 'requirements', 'priority', 'status', 'payment_authorization', 'currency', 'original_price', 'final_price', 'discount_amount', 'tax_rate', 'tax_amount', 'total', 'amount_paid', 'amount_due', 'expected_cost', 'actual_cost', 'urgency', 'preferred_date', 'customer_notes', 'internal_notes', 'customer_accepted_at', 'employee_approved_at', 'price_locked', 'final_price_accepted_by', 'verification_snapshot', 'manager_override_by', 'manager_override_at', 'manager_override_reason', 'attachments', 'closed_at', 'closure_notes', 'task_completed_at'];
    protected $casts = ['customer_id' => 'integer', 'service_id' => 'integer', 'created_by' => 'integer', 'assigned_to' => 'integer', 'final_price_accepted_by' => 'integer', 'manager_override_by' => 'integer', 'original_price' => 'decimal:2', 'final_price' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_rate' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total' => 'decimal:2', 'amount_paid' => 'decimal:2', 'amount_due' => 'decimal:2', 'expected_cost' => 'decimal:2', 'actual_cost' => 'decimal:2', 'customer_accepted_at' => 'datetime', 'employee_approved_at' => 'datetime', 'price_locked' => 'boolean', 'manager_override_at' => 'datetime', 'closed_at' => 'datetime', 'task_completed_at' => 'datetime', 'preferred_date' => 'date', 'attachments' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->order_number)) {
                // Retry on unique collision (concurrent creates). order_number has a unique index.
                for ($attempt = 0; $attempt < 5; $attempt++) {
                    $max = (int) static::whereYear('created_at', now()->year)
                        ->where('order_number', 'like', 'ORD-' . now()->format('Y') . '-%')
                        ->max(\Illuminate\Support\Facades\DB::raw('CAST(SUBSTR(order_number, -6) AS INTEGER)'));
                    $next = $max + 1;
                    // Fall back to count-based seed when no parsable numbers exist yet.
                    if ($next <= 1) {
                        $next = static::whereYear('created_at', now()->year)->count('id') + 1 + $attempt;
                    } else {
                        $next += $attempt;
                    }
                    $candidate = 'ORD-' . now()->format('Y') . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                    if (!static::where('order_number', $candidate)->exists()) {
                        $order->order_number = $candidate;
                        break;
                    }
                }
            }
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function service() { return $this->belongsTo(Service::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function finalPriceAccepter() { return $this->belongsTo(User::class, 'final_price_accepted_by'); }
    public function managerOverride() { return $this->belongsTo(User::class, 'manager_override_by'); }
    public function priceRevisions() { return $this->hasMany(ServiceOrderPriceRevision::class)->latest(); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function receipts() { return $this->hasMany(Receipt::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function expenses() { return $this->hasMany(Expense::class); }

    public function getIsFullyVerifiedAttribute(): bool
    {
        return (bool) ($this->customer?->isFullyVerified());
    }

    public function canStartWork(): bool
    {
        return in_array($this->payment_authorization, ['ready_to_start', 'deposit_received', 'fully_paid', 'manager_override'], true);
    }

    public function requiresDeposit(): bool
    {
        return in_array($this->payment_authorization, ['not_authorized', 'deposit_required'], true);
    }

    public function isFullyPaid(): bool
    {
        return (float) $this->amount_due <= 0 && (float) $this->total > 0;
    }

    public function getOutstandingBalanceAttribute(): string
    {
        return (string) max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->actual_cost > 0 ? $this->actual_cost : $this->expenses()->sum('amount'));
    }

    public function getActualProfitAttribute(): float
    {
        return (float) $this->amount_paid - $this->total_cost;
    }

    public function getExpectedProfitAttribute(): float
    {
        return (float) $this->total - (float) $this->expected_cost;
    }

    public function getCollectedRevenueAttribute(): float
    {
        return (float) $this->amount_paid;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ((float) $this->amount_paid <= 0) return 'unpaid';
        if ((float) $this->amount_due <= 0) return 'paid';
        return 'partially_paid';
    }
}
