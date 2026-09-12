<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiEscalation extends Model
{
    protected $fillable = [
        'conversation_id', 'ticket_id', 'assigned_to', 'reason',
        'context_summary', 'status', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function conversation() { return $this->belongsTo(AiConversation::class, 'conversation_id'); }
    public function ticket() { return $this->belongsTo(Ticket::class, 'ticket_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }

    public function resolve()
    {
        $this->update(['status' => 'resolved', 'resolved_at' => now()]);
    }
}
