<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'quotation_number', 'customer_id', 'company_id', 'service_request_id',
        'notes', 'terms', 'subtotal', 'discount_amount', 'tax_rate', 'tax_amount',
        'total', 'status', 'valid_until', 'sent_at', 'viewed_at', 'accepted_at',
        'currency', 'country_id', 'assigned_to',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'company_id' => 'integer',
        'service_request_id' => 'integer',
        'country_id' => 'integer',
        'assigned_to' => 'integer',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($q) {
            if (empty($q->quotation_number)) {
                $q->quotation_number = 'QT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeForCustomer($query, $userId)
    {
        return $query->where('customer_id', $userId);
    }

    public function getCurrencySymbolAttribute(): string
    {
        if ($this->country_id) {
            $country = Country::find($this->country_id);
            return $country ? $country->currency_symbol : '$';
        }
        return '$';
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->currency_symbol . number_format($this->total, 2);
    }
}
