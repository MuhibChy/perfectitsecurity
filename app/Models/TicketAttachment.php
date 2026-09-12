<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = ['ticket_id', 'message_id', 'uploaded_by', 'filename', 'original_name', 'mime_type', 'size', 'path'];
    protected $casts = ['ticket_id' => 'integer', 'message_id' => 'integer', 'uploaded_by' => 'integer', 'size' => 'integer'];
    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function message() { return $this->belongsTo(TicketMessage::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
