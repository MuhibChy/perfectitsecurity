<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ConfigurationItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ci_number', 'name', 'ci_type', 'customer_id', 'company_id', 'asset_id',
        'identifier', 'environment', 'status', 'criticality', 'owner_id', 'details',
    ];

    protected $casts = ['details' => 'array'];

    protected static function booted()
    {
        static::creating(function ($ci) {
            if (empty($ci->ci_number)) {
                $ci->ci_number = 'CI-' . strtoupper(Str::random(8));
            }
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function company() { return $this->belongsTo(Company::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function childRelationships() { return $this->hasMany(CiRelationship::class, 'parent_ci_id'); }
    public function parentRelationships() { return $this->hasMany(CiRelationship::class, 'child_ci_id'); }

    public function scopeForCustomer($query, $customerId) { return $query->where('customer_id', $customerId); }
}
