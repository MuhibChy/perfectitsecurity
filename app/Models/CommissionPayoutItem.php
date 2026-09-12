<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPayoutItem extends Model
{
    protected $fillable = ['payout_id', 'commission_id', 'amount'];
    public function payout() { return $this->belongsTo(CommissionPayout::class, 'payout_id'); }
    public function commission() { return $this->belongsTo(Commission::class); }
}
