<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = ['name', 'description', 'response_time_minutes', 'resolution_time_minutes', 'priority', 'is_active'];
    public function ticketCategories() { return $this->hasMany(TicketCategory::class, 'sla_policy_id'); }
    public function tickets() { return $this->hasMany(Ticket::class, 'sla_policy_id'); }
}
