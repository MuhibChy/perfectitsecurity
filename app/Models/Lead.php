<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'lead_number', 'service_request_id', 'customer_id', 'company_id', 'assigned_to',
        'name', 'email', 'phone', 'company_name', 'source', 'status', 'priority',
        'estimated_value', 'currency', 'country_id', 'notes', 'tags',
        'next_follow_up_at', 'converted_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'tags' => 'array',
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($lead) {
            if (empty($lead->lead_number)) {
                $lead->lead_number = 'LD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
