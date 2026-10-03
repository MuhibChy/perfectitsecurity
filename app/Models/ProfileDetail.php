<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Extended customer/employee profile fields. One row per user; all nullable. */
class ProfileDetail extends Model
{
    public const CONTACT_METHODS = ['website', 'email', 'phone', 'whatsapp', 'video', 'sms'];

    protected $fillable = [
        'user_id', 'secondary_phone', 'whatsapp_number', 'preferred_contact_method',
        'contact_hours', 'availability_note', 'team', 'skills', 'certifications',
        'expertise', 'manager_id', 'hire_date', 'termination_date', 'employment_status',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'business_info', 'internal_notes',
    ];

    protected $casts = [
        'skills' => 'array',
        'certifications' => 'array',
        'expertise' => 'array',
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
}
