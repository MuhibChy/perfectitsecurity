<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthCheckLog extends Model
{
    use HasFactory;

    protected $table = 'health_check_logs';

    protected $fillable = [
        'check_name',
        'check_type',
        'endpoint',
        'status_code',
        'response_time',
        'is_healthy',
        'error_message',
        'response_body',
    ];

    protected $casts = [
        'is_healthy' => 'boolean',
        'status_code' => 'integer',
        'response_time' => 'integer',
    ];
}
