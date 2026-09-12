<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderPriceRevision extends Model
{
    protected $fillable = ['service_order_id', 'proposed_by', 'kind', 'amount', 'discount_amount', 'tax_rate', 'terms', 'status'];
    protected $casts = ['amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_rate' => 'decimal:2'];
    public function order() { return $this->belongsTo(ServiceOrder::class, 'service_order_id'); }
    public function proposer() { return $this->belongsTo(User::class, 'proposed_by'); }
}
