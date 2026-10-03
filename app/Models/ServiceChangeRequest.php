<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceChangeRequest extends Model
{
    protected $fillable = ['order_id', 'project_id', 'requested_by', 'title', 'details', 'price_impact', 'time_impact_days', 'status', 'reviewed_by', 'decided_at', 'decision_note'];
    protected $casts = ['price_impact' => 'decimal:2', 'decided_at' => 'datetime'];

    public const STATUSES = ['requested', 'under_review', 'approved', 'rejected', 'implemented'];

    public function order() { return $this->belongsTo(ServiceOrder::class, 'order_id'); }
    public function project() { return $this->belongsTo(Project::class, 'project_id'); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
