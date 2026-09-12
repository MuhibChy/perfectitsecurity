<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id', 'user_id', 'guest_name', 'guest_email', 'status',
        'source', 'context', 'related_ticket_id', 'escalated_to',
        'escalated_at', 'closed_at', 'language', 'message_count', 'satisfied',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'related_ticket_id' => 'integer',
        'escalated_to' => 'integer',
        'context' => 'array',
        'escalated_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfied' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($conv) {
            if (empty($conv->session_id)) {
                $conv->session_id = Str::uuid()->toString();
            }
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(AiMessage::class, 'conversation_id'); }
    public function escalations() { return $this->hasMany(AiEscalation::class, 'conversation_id'); }
    public function relatedTicket() { return $this->belongsTo(Ticket::class, 'related_ticket_id'); }
    public function escalatedTo() { return $this->belongsTo(User::class, 'escalated_to'); }
    public function usageRecords() { return $this->hasMany(AiUsageRecord::class, 'conversation_id'); }

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeForUser($q, $userId) { return $q->where('user_id', $userId); }
    public function scopePublic($q) { return $q->where('source', 'public'); }

    public function close()
    {
        $this->update(['status' => 'closed', 'closed_at' => now()]);
    }

    public function escalate($reason, $ticketId = null)
    {
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
            'related_ticket_id' => $ticketId,
        ]);

        return AiEscalation::create([
            'conversation_id' => $this->id,
            'ticket_id' => $ticketId,
            'reason' => $reason,
            'context_summary' => $this->messages->last()?->content,
        ]);
    }
}
