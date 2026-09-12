<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    protected $fillable = [
        'is_demo',
        'request_number', 'user_id', 'service_id', 'country_id', 'currency',
        'name', 'email', 'phone', 'company', 'subject', 'service_interest',
        'requirements', 'budget', 'budget_range', 'timeline', 'lead_source',
        'quoted_price', 'currency_symbol', 'preferred_start_date', 'status',
        'assigned_to', 'internal_notes', 'attachment_paths', 'scope_details',
        'exclusions', 'estimated_delivery', 'priority', 'quotation_id',
        'review_status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'attachment_paths' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($sr) {
            if (empty($sr->request_number)) {
                $sr->request_number = 'SR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function scopeForCustomer($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
