<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTeam extends Model
{
    protected $fillable = ['name', 'description', 'lead_id', 'is_active'];
    public function lead() { return $this->belongsTo(User::class, 'lead_id'); }
    public function tickets() { return $this->hasMany(Ticket::class, 'team_id'); }
}
