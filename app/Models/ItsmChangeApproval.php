<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItsmChangeApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'itsm_change_id', 'approver_id', 'decision', 'comments', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function change()
    {
        return $this->belongsTo(ItsmChange::class, 'itsm_change_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
