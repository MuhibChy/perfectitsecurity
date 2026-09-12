<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'user_id', 'name', 'original_name', 'mime_type', 'size',
        'path', 'category', 'related_project_id', 'related_ticket_id',
        'related_invoice_id', 'description',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'related_project_id' => 'integer',
        'related_ticket_id' => 'integer',
        'related_invoice_id' => 'integer',
        'size' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function project() { return $this->belongsTo(Project::class, 'related_project_id'); }
    public function ticket() { return $this->belongsTo(Ticket::class, 'related_ticket_id'); }
    public function invoice() { return $this->belongsTo(Invoice::class, 'related_invoice_id'); }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'pdf') => 'pdf',
            str_contains($this->mime_type, 'image') => 'image',
            str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => 'doc',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'xls',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') => 'zip',
            default => 'file',
        };
    }

    public function scopeForCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
