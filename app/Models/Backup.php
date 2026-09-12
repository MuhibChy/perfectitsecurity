<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Backup extends Model
{
    protected $fillable = [
        'backup_id', 'type', 'scope', 'status', 'started_at', 'completed_at',
        'duration_ms', 'size_bytes', 'storage', 'storage_path', 'encrypted',
        'encryption_cipher', 'encryption_key_fingerprint', 'checksum',
        'db_version', 'app_version', 'created_by', 'origin', 'error_message',
        'verification_status', 'verified_at', 'verification_detail',
        'restore_test_status', 'restore_tested_at', 'retain_until',
        'retention_hold', 'deleted_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'restore_tested_at' => 'datetime',
        'retain_until' => 'datetime',
        'deleted_at' => 'datetime',
        'encrypted' => 'boolean',
        'retention_hold' => 'boolean',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(BackupFile::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified' && $this->verification_status === 'passed';
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified')->where('verification_status', 'passed');
    }
}
