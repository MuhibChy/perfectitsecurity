<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_demo',
        'invoice_number', 'customer_id', 'company_id', 'project_id', 'service_order_id',
        'quotation_id', 'subscription_id', 'notes', 'terms', 'subtotal', 'discount_amount',
        'discount_type', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'amount_due', 'currency', 'status', 'issued_date', 'due_date', 'sent_at', 'paid_at',
        'stripe_checkout_session_id', 'stripe_payment_intent_id',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'company_id' => 'integer',
        'project_id' => 'integer',
        'service_order_id' => 'integer',
        'quotation_id' => 'integer',
        'subscription_id' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'issued_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function company() { return $this->belongsTo(Company::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function financialTransactions() { return $this->hasMany(FinancialTransaction::class); }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['paid', 'cancelled', 'refunded']);
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->due_date || in_array($this->status, ['paid', 'cancelled'])) return null;
        return max(0, now()->diffInDays($this->due_date, false));
    }

    public function recalculate()
    {
        $this->subtotal = $this->items->sum('total');
        $discount = $this->discount_type === 'percentage'
            ? $this->subtotal * ($this->discount_amount / 100)
            : $this->discount_amount;
        $afterDiscount = $this->subtotal - $discount;
        $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        $this->total = $afterDiscount + $this->tax_amount;
        $this->amount_due = $this->total - $this->amount_paid;
        $this->save();
    }
}
