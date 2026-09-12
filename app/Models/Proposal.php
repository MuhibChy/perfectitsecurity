<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Proposal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'proposal_number', 'customer_id', 'lead_id', 'quotation_id', 'created_by',
        'title', 'status', 'version', 'scope_of_work', 'deliverables', 'timeline',
        'terms', 'subtotal', 'tax_rate', 'tax_amount', 'total', 'currency',
        'valid_until', 'sent_at', 'accepted_at', 'rejected_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($p) {
            if (empty($p->proposal_number)) {
                $p->proposal_number = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function sections()
    {
        return $this->hasMany(ProposalSection::class)->orderBy('sort_order');
    }

    public function versions()
    {
        return $this->hasMany(ProposalVersion::class)->orderByDesc('version');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
