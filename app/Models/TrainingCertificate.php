<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingCertificate extends Model
{
    protected $fillable = ['course_id', 'user_id', 'score', 'certificate_no', 'course_version', 'completed_at', 'status', 'revoked_at', 'revoked_by', 'revoke_reason'];
    protected $casts = ['completed_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function course() { return $this->belongsTo(TrainingCourse::class, 'course_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}
