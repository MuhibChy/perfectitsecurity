<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RemoteSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_number', 'ticket_id', 'customer_id', 'technician_id', 'provider',
        'session_url', 'consent_given', 'consent_at', 'consent_by', 'status',
        'scheduled_at', 'started_at', 'ended_at', 'outcome',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($session) {
            if (empty($session->session_number)) {
                $session->session_number = 'RS-' . strtoupper(Str::random(8));
            }
        });
    }

    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function consenter() { return $this->belongsTo(User::class, 'consent_by'); }

    public function scopeForCustomer($query, $customerId) { return $query->where('customer_id', $customerId); }
}
