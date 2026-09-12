<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subscription_number', 'customer_id', 'service_id', 'invoice_id', 'name',
        'status', 'interval', 'interval_count', 'amount', 'currency', 'tax_rate',
        'stripe_subscription_id', 'stripe_customer_id', 'starts_at', 'next_billing_at',
        'ends_at', 'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'starts_at' => 'date',
        'next_billing_at' => 'date',
        'ends_at' => 'date',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($s) {
            if (empty($s->subscription_number)) {
                $s->subscription_number = 'SUB-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
            ->whereDate('next_billing_at', '<=', now()->toDateString());
    }
}
