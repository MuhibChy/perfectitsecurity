<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'contract_number', 'customer_id', 'company_id', 'proposal_id', 'quotation_id',
        'created_by', 'title', 'status', 'body', 'value', 'currency',
        'start_date', 'end_date', 'signed_at', 'signed_by_name',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($c) {
            if (empty($c->contract_number)) {
                $c->contract_number = 'CT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
