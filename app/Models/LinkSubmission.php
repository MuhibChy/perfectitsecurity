<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'description',
        'category',
        'submitter_name',
        'submitter_email',
        'status',
        'admin_notes',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
