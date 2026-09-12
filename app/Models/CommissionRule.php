<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = ['name', 'description', 'type', 'rate', 'tiers', 'minimum_threshold', 'maximum_payout', 'is_active'];
    protected $casts = ['tiers' => 'array', 'rate' => 'decimal:2', 'minimum_threshold' => 'decimal:2', 'maximum_payout' => 'decimal:2'];
    public function commissions() { return $this->hasMany(Commission::class, 'rule_id'); }
}
