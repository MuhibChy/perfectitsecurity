<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceEvent extends Model
{
    protected $fillable = ['entity_type', 'entity_id', 'order_id', 'project_id', 'customer_id', 'actor_id', 'action', 'old_value', 'new_value', 'reason', 'comment', 'customer_visible', 'metadata'];

    protected $casts = ['customer_visible' => 'boolean', 'metadata' => 'array'];

    public const ACTIONS = [
        'created', 'status_changed', 'assigned', 'started', 'paused', 'resumed',
        'waiting_for_customer', 'progress_updated', 'eta_changed', 'update_published',
        'update_requested', 'query_asked', 'query_answered', 'completed', 'reopened',
        'change_requested', 'change_decided', 'maintenance_scheduled', 'maintenance_completed',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function scopeVisibleToCustomer($q)
    {
        return $q->where('customer_visible', true);
    }
}
