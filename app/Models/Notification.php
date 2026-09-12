<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['is_demo', 'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];
    protected $casts = ['data' => 'array', 'read_at' => 'datetime'];

    public function notifiable() { return $this->morphTo(); }

    public function scopeUnread($q) { return $q->whereNull('read_at'); }
    public function markAsRead() { $this->update(['read_at' => now()]); }
}
