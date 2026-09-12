<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCountryPrice extends Model
{
    protected $fillable = [
        'service_id', 'country_id', 'pricing_type', 'price',
        'discount_price', 'discount_valid_until', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_valid_until' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->discount_price && $this->discount_valid_until && $this->discount_valid_until->isFuture()) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }
}
