<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'route',
        'error_type',
        'message',
        'file',
        'line',
        'trace',
        'status',
        'occurrences',
        'notes',
        'first_seen_at',
        'last_seen_at',
        'resolved_at',
    ];

    protected $casts = [
        'line' => 'integer',
        'occurrences' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scopeUnresolved($query)
    {
        return $query->where('status', 'unresolved');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
