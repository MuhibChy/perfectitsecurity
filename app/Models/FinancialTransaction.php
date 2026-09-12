<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'transaction_id', 'type', 'category', 'description', 'amount',
        'running_balance', 'currency', 'customer_id', 'vendor_id', 'invoice_id',
        'payment_id', 'project_id', 'ticket_id', 'employee_id', 'commission_id', 'service_order_id',
        'expense_id', 'payment_method', 'status', 'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($txn) {
            if (empty($txn->transaction_id)) {
                $txn->transaction_id = 'TXN-' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            $lastBalance = static::where('status', 'completed')->latest('id')->value('running_balance') ?? 0;
            $txn->running_balance = in_array($txn->type, ['expense', 'refund', 'commission', 'salary'])
                ? $lastBalance - $txn->amount
                : $lastBalance + $txn->amount;
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeIncome($q) { return $q->where('type', 'income'); }
    public function scopeExpenses($q) { return $q->where('type', 'expense'); }
    public function scopeDateRange($q, $from, $to) { return $q->whereBetween('created_at', [$from, $to]); }
}
