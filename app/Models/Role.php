<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'status', 'registration_allowed', 'approval_required', 'self_registration', 'dashboard_route', 'dashboard_label', 'permissions', 'training_slugs', 'sort_order'];
    protected $casts = ['registration_allowed' => 'boolean', 'approval_required' => 'boolean', 'self_registration' => 'boolean', 'permissions' => 'array', 'training_slugs' => 'array'];

    public const STATUSES = ['active', 'inactive', 'deprecated', 'archived'];

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeRegisterable($q) { return $q->active()->where('registration_allowed', true)->orderBy('sort_order'); }

    public function users() { return $this->hasMany(User::class, 'role', 'name'); }

    public function isRetired(): bool
    {
        return in_array($this->status, ['deprecated', 'archived'], true);
    }
}
