<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag', 'name', 'category', 'manufacturer', 'model', 'serial_number',
        'customer_id', 'company_id', 'location', 'assigned_to_user', 'status',
        'purchase_date', 'warranty_expires', 'retirement_date', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expires' => 'date',
        'retirement_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = 'AST-'.strtoupper(Str::random(8));
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

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user');
    }

    public function configurationItems()
    {
        return $this->hasMany(ConfigurationItem::class, 'asset_id');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
