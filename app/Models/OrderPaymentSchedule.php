<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentSchedule extends Model
{
    protected $fillable = ['order_id', 'milestone_id', 'title', 'expected_amount', 'paid_amount', 'status', 'due_at', 'sort_order', 'created_by'];

    protected $casts = ['expected_amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_at' => 'date'];

    public const STATUSES = ['pending', 'partial', 'paid', 'waived'];

    public function order()
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'schedule_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function remaining(): float
    {
        return max(0, round((float) $this->expected_amount - (float) $this->paid_amount, 2));
    }
}
