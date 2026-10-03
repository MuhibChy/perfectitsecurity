<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DirectMessage extends Model
{
    protected $fillable = ['uuid', 'sender_id', 'recipient_id', 'parent_id', 'subject', 'body', 'is_internal', 'is_customer_visible', 'read_at', 'related_type', 'related_id'];
    protected $casts = ['is_internal' => 'boolean', 'is_customer_visible' => 'boolean', 'read_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });
    }

    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies() { return $this->hasMany(self::class, 'parent_id'); }
    /** Optional link of this conversation to its ticket/project/order. */
    public function related() { return $this->morphTo(); }

    /** Customer-visible thread view: hides internal staff notes from customers. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isCustomer()) {
            return $query->where('is_customer_visible', true)->where('is_internal', false);
        }
        return $query;
    }
}
