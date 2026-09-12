<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AiUsageRecord extends Model
{
    protected $fillable = [
        'user_id', 'conversation_id', 'provider', 'model',
        'input_tokens', 'output_tokens', 'cost', 'request_type',
        'success', 'error_message', 'response_time_ms', 'recorded_date',
    ];

    protected $casts = ['recorded_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function conversation() { return $this->belongsTo(AiConversation::class, 'conversation_id'); }

    public static function getDailyCost(?Carbon $date = null)
    {
        $date = $date ?? now();
        return static::whereDate('recorded_date', $date)->sum('cost');
    }

    public static function getMonthlyCost(?Carbon $date = null)
    {
        $date = $date ?? now();
        return static::whereYear('recorded_date', $date->year)
            ->whereMonth('recorded_date', $date->month)
            ->sum('cost');
    }
}
