<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name', 'code', 'currency_code', 'currency_symbol', 'currency_name',
        'tax_rate', 'timezone', 'business_hours_start', 'business_hours_end',
        'business_days', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'business_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function servicePrices()
    {
        return $this->hasMany(ServiceCountryPrice::class, 'country_id');
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
