<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'is_demo',
        'name', 'slug', 'email', 'phone', 'address', 'city', 'state',
        'zip_code', 'country', 'website', 'logo', 'tax_number',
        'credit_limit', 'status',
    ];

    public function users() { return $this->hasMany(User::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
