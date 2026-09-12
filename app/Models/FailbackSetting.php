<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailbackSetting extends Model
{
    use HasFactory;

    protected $table = 'failback_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];
}
