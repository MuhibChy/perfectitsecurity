<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agreement_number', 'customer_id', 'company_id', 'title', 'scope',
        'coverage_hours', 'response_target_minutes', 'resolution_target_minutes',
        'starts_at', 'ends_at', 'status', 'renewal_reminder_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'renewal_reminder_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($agreement) {
            if (empty($agreement->agreement_number)) {
                $agreement->agreement_number = 'AGR-' . strtoupper(Str::random(8));
            }
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function company() { return $this->belongsTo(Company::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeForCustomer($query, $customerId) { return $query->where('customer_id', $customerId); }
}
