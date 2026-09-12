<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = ['quotation_id', 'service_id', 'description', 'quantity', 'unit_price', 'discount', 'tax_rate', 'total'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function service() { return $this->belongsTo(Service::class); }
}
