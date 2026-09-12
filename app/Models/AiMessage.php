<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'role', 'content', 'metadata', 'sources',
        'tokens_used', 'cost', 'response_time_ms',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sources' => 'array',
    ];

    public function conversation() { return $this->belongsTo(AiConversation::class, 'conversation_id'); }
}
