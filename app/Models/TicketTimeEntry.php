<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTimeEntry extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'minutes', 'description', 'date'];
    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function user() { return $this->belongsTo(User::class); }
}
