<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sla_policy_id', 'is_active'];
    public function slaPolicy() { return $this->belongsTo(SlaPolicy::class); }
    public function subcategories() { return $this->hasMany(TicketSubcategory::class, 'category_id'); }
    public function tickets() { return $this->hasMany(Ticket::class); }
}
