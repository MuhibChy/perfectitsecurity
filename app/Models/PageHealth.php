<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageHealth extends Model
{
    use HasFactory;

    protected $table = 'page_healths';

    protected $fillable = [
        'route_name',
        'uri',
        'module',
        'method',
        'role_tested',
        'http_status',
        'response_time_ms',
        'status',
        'error_summary',
        'last_checked_at',
        'last_success_at',
        'last_failure_at',
    ];

    protected $casts = [
        'http_status' => 'integer',
        'response_time_ms' => 'integer',
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'Healthy', 'healthy' => 'badge-emerald',
            'Warning', 'warning' => 'badge-amber',
            'Error', 'error' => 'badge-red',
            'Unavailable', 'unavailable' => 'badge-gray',
            default => 'badge-gray',
        };
    }
}
