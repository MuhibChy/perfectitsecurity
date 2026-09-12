<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSubcategory extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'is_active'];
    public function category() { return $this->belongsTo(TicketCategory::class, 'category_id'); }
}
