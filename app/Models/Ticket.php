<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_demo',
        'ticket_number', 'customer_id', 'company_id', 'service_order_id', 'subject', 'description',
        'category_id', 'subcategory_id', 'priority', 'severity', 'impact',
        'urgency', 'status', 'assigned_to', 'team_id', 'tags', 'sla_policy_id',
        'sla_response_deadline', 'sla_resolution_deadline', 'first_response_at',
        'sla_warning_sent_at', 'sla_breached_at',
        'resolved_at', 'closed_at', 'resolution_details', 'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'company_id' => 'integer',
        'service_order_id' => 'integer',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'assigned_to' => 'integer',
        'team_id' => 'integer',
        'sla_policy_id' => 'integer',
        'sla_response_deadline' => 'datetime',
        'sla_resolution_deadline' => 'datetime',
        'sla_warning_sent_at' => 'datetime',
        'sla_breached_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'tags' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TK-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function company() { return $this->belongsTo(Company::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function team() { return $this->belongsTo(SupportTeam::class, 'team_id'); }
    public function category() { return $this->belongsTo(TicketCategory::class); }
    public function subcategory() { return $this->belongsTo(TicketSubcategory::class); }
    public function slaPolicy() { return $this->belongsTo(SlaPolicy::class); }
    public function messages() { return $this->hasMany(TicketMessage::class); }
    public function attachments() { return $this->hasMany(TicketAttachment::class); }
    public function timeEntries() { return $this->hasMany(TicketTimeEntry::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }

    public function scopeOpen($query) { return $query->whereNotIn('status', ['resolved', 'closed', 'cancelled']); }
    public function scopeForCustomer($query, $customerId) { return $query->where('customer_id', $customerId); }
    public function scopeForAgent($query, $agentId) { return $query->where('assigned_to', $agentId); }

    public function getSlaStatusAttribute()
    {
        if (!$this->sla_resolution_deadline) return null;
        $now = now();
        $deadline = $this->sla_resolution_deadline;
        if ($this->status === 'resolved' || $this->status === 'closed') {
            return $this->resolved_at && $this->resolved_at->lte($deadline) ? 'met' : 'breached';
        }
        if ($now->gt($deadline)) return 'breached';
        if ($now->diffInHours($deadline) < 2) return 'warning';
        return 'ok';
    }

    public function getTotalTimeTrackedAttribute()
    {
        return $this->timeEntries->sum('minutes');
    }
}
