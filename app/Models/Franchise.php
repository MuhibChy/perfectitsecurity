<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Franchise organization entity (Phase 14-15).
 *
 * Deliberately NOT a login role: people authenticate with their person
 * role (owner/manager/staff/agent) and link via users.franchise_id, so
 * the RBAC matrix stays intact. Future extension path (no schema change
 * needed): add 'franchise_owner' | 'franchise_manager' | 'franchise_staff'
 * roles to RoleRegistry + isFranchise*() gates scoped by franchise_id,
 * plus a franchise self-service portal reusing PeopleController@show with
 * a franchise-membership gate.
 */
class Franchise extends Model
{
    protected $fillable = ['franchise_code', 'name', 'legal_name', 'owner_id', 'territory', 'contact_email', 'contact_phone', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $f) {
            if (empty($f->franchise_code)) $f->franchise_code = 'FR-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        });
    }

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function members() { return $this->hasMany(User::class); }
    public function transfers() { return $this->hasMany(BankTransfer::class); }
}
