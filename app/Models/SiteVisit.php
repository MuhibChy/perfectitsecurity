<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SiteVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_number', 'ticket_id', 'customer_id', 'company_id', 'address',
        'scheduled_at', 'technician_id', 'status', 'check_in_at', 'check_out_at',
        'work_performed', 'parts_used', 'customer_signature_name',
        'customer_confirmed_at', 'follow_up_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $visit->visit_number = 'VST-'.strtoupper(Str::random(8));
            }
        });
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
