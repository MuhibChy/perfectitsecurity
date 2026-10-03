<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaBreachLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'sla_policy_id', 'breach_type', 'deadline', 'breached_at',
        'acknowledged_by', 'acknowledged_at', 'notes',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'breached_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
