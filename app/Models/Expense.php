<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'expense_number', 'category_id', 'category_name', 'description',
        'amount', 'date', 'vendor', 'payment_method', 'receipt_path',
        'status', 'approved_by', 'approved_at', 'project_id', 'service_order_id',
        'task_id', 'worker_id', 'cost_type', 'hours', 'hourly_rate',
        'commission_percentage', 'commission_amount',
        'is_recurring', 'recurrence_pattern', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP-' . date('Y') . '-' . str_pad((string) (static::max('id') + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function category() { return $this->belongsTo(ExpenseCategory::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function worker() { return $this->belongsTo(User::class, 'worker_id'); }
    public function project() { return $this->belongsTo(Project::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function task() { return $this->belongsTo(Task::class); }
}
