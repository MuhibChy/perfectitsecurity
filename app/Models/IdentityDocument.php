<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Identity verification submission. History is never overwritten:
 * rejection/resubmission creates new rows; old rows stay as audit trail.
 * Files live on the private disk; this row carries metadata only.
 */
class IdentityDocument extends Model
{
    public const STATUSES = ['submitted', 'under_review', 'verified', 'rejected', 'resubmission_required', 'expired', 'revoked'];

    public const TYPES = ['national_id', 'passport', 'driving_licence', 'other'];

    protected $fillable = [
        'user_id', 'document_type', 'issuing_country', 'expiry_date',
        'path', 'original_name', 'status', 'reviewer_id', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = ['expiry_date' => 'date', 'reviewed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
