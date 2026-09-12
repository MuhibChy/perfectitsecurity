<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupFile extends Model
{
    protected $fillable = [
        'backup_id', 'kind', 'disk', 'path', 'size_bytes', 'checksum',
        'encrypted', 'file_count',
    ];

    protected $casts = [
        'encrypted' => 'boolean',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }
}
