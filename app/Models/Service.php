<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'category_id', 'name', 'slug', 'subcategory', 'short_description',
        'description', 'full_description', 'deliverables', 'scope', 'exclusions',
        'process_steps', 'icon', 'image', 'price_type', 'complexity_level',
        'starting_price', 'hourly_rate', 'allows_custom_quote', 'estimated_completion',
        'features', 'faq', 'is_featured', 'is_active', 'sort_order',
        'seo_title', 'seo_description', 'tags',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'features' => 'array',
        'faq' => 'array',
        'deliverables' => 'array',
        'process_steps' => 'array',
        'tags' => 'array',
    ];

    public function getBasePriceAttribute()
    {
        return $this->attributes['starting_price'] ?? 0;
    }

    public function setBasePriceAttribute($value): void
    {
        $this->attributes['starting_price'] = $value;
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function relatedServices()
    {
        return $this->belongsToMany(Service::class, 'related_services', 'service_id', 'related_service_id');
    }

    public function countryPrices()
    {
        return $this->hasMany(ServiceCountryPrice::class, 'service_id');
    }

    public function priceForCountry($countryId)
    {
        return $this->countryPrices()
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->first();
    }

    public function getPriceFormatted($countryId): string
    {
        $countryPrice = $this->priceForCountry($countryId);
        if (!$countryPrice) {
            return 'Contact for pricing';
        }

        $country = $countryPrice->country;
        $price = $countryPrice->effective_price;
        $symbol = $country->currency_symbol;

        return match ($countryPrice->pricing_type) {
            'fixed' => $symbol . number_format($price, 0),
            'starting_from' => 'From ' . $symbol . number_format($price, 0),
            'hourly' => $symbol . number_format($price, 2) . '/hr',
            'daily' => $symbol . number_format($price, 0) . '/day',
            'monthly' => $symbol . number_format($price, 0) . '/mo',
            'recurring' => $symbol . number_format($price, 0) . '/mo',
            'custom_quote' => 'Custom Quote',
            default => $symbol . number_format($price, 0),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
