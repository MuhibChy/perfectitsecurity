<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMaintenance extends Model
{
    protected $fillable = ['project_id', 'order_id', 'customer_id', 'title', 'type', 'starts_at', 'ends_at', 'frequency', 'next_due_at', 'assigned_to', 'status', 'notes'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'next_due_at' => 'date'];

    public const STATUSES = ['scheduled', 'active', 'completed', 'cancelled'];

    public function project() { return $this->belongsTo(Project::class); }
    public function order() { return $this->belongsTo(ServiceOrder::class, 'order_id'); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
