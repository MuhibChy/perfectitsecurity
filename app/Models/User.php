<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'is_demo',
        'name', 'email', 'phone', 'password', 'role', 'avatar',
        'requested_role', 'role_approval_status', 'role_approved_by', 'role_approved_at',
        'approved_by', 'approved_at', 'approval_note',
        'company_name', 'address', 'city', 'state', 'zip_code',
        'country', 'country_code', 'preferred_currency', 'preferred_locale', 'stripe_customer_id',
        'glove_mode', 'glove_x', 'glove_y',
        'is_active', 'two_factor_enabled', 'two_factor_secret', 'two_factor_confirmed_at',
        'last_login_at', 'last_login_ip', 'company_id', 'franchise_id', 'email_verified_at', 'phone_verified_at', 'verification_status',
        'timezone', 'job_title', 'department', 'branch', 'employee_number',
        'email_otp_hash', 'email_otp_expires_at', 'email_otp_attempts', 'email_otp_sent_at',
        'phone_otp_hash', 'phone_otp_expires_at', 'phone_otp_attempts', 'phone_otp_sent_at',
        'presence', 'presence_visible',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'phone_otp_hash', 'email_otp_hash',
    ];

    /** In-memory defaults mirror the DB defaults for freshly built instances. */
    protected $attributes = [
        'identity_status' => 'not_started',
        'presence' => 'offline',
        'presence_visible' => true,
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_otp_expires_at' => 'datetime',
        'phone_otp_sent_at' => 'datetime',
        'phone_otp_attempts' => 'integer',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'email_otp_hash' => 'string',
        'email_otp_expires_at' => 'datetime',
        'email_otp_attempts' => 'integer',
        'email_otp_sent_at' => 'datetime',
        'role_approved_at' => 'datetime',
        'password' => 'string',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        // Secrets encrypted at rest; recovery codes are bcrypt hashes (never plaintext).
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'identity_verified_at' => 'datetime',
        'presence_visible' => 'boolean',
    ];

    public function hasMfaEnabled(): bool
    {
        return (bool) $this->two_factor_enabled && !empty($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    public function isSalesAgent(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'sales_agent', 'finance_manager'], true);
    }

    // Verification checks
    public function isEmailVerified(): bool { return !is_null($this->email_verified_at); }
    public function isPhoneVerified(): bool { return !is_null($this->phone_verified_at); }
    public function isFullyVerified(): bool
    {
        return $this->isEmailVerified() && $this->isPhoneVerified() && !in_array($this->verification_status, ['suspended', 'blocked'], true);
    }
    public function isPendingVerification(): bool { return !$this->isFullyVerified() && !in_array($this->verification_status, ['suspended', 'blocked'], true); }
    public function isSuspended(): bool { return $this->verification_status === 'suspended'; }
    public function isBlocked(): bool { return $this->verification_status === 'blocked'; }

    // Role checks
    public function isSuperAdmin() { return $this->role === 'super_admin'; }
    public function isAdmin() { return in_array($this->role, ['super_admin', 'admin']); }
    public function isFinanceManager() { return in_array($this->role, ['super_admin', 'admin', 'finance_manager']); }
    public function isSupportManager() { return in_array($this->role, ['super_admin', 'admin', 'support_manager']); }
    public function isSupportAgent() { return in_array($this->role, ['super_admin', 'admin', 'support_manager', 'support_agent']); }
    public function isProjectManager() { return in_array($this->role, ['super_admin', 'admin', 'project_manager']); }
    public function isEmployee() { return in_array($this->role, ['super_admin', 'admin', 'employee', 'support_agent', 'project_manager', 'support_manager', 'finance_manager', 'sales_agent']); }
    public function isFreelancer() { return in_array($this->role, ['freelancer', 'commission_agent']); }
    public function isCustomer() { return $this->role === 'customer'; }
    public function isStaff() { return !$this->isCustomer() && !$this->isFreelancer(); }
    // Trainer / Academy access (admins inherit).
    public function isTrainingManager() { return in_array($this->role, ['super_admin', 'admin', 'training_manager']); }
    public function isTrainer() { return $this->isTrainingManager(); }

    /** Central-registry capability check (descriptive layer over is* gates). */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, \App\Support\RoleRegistry::capabilitiesOf($this->role), true);
    }

    /**
     * Allowed currencies for this account: at most the supported local
     * currency plus USD (USD-only when the country is unsupported).
     * Delegates to the centralized CustomerCurrencyService.
     *
     * @return string[]
     */
    public function availableCurrencies(): array
    {
        return app(\App\Services\CustomerCurrencyService::class)->availableFor($this);
    }

    /** Supported local currency code, or null when USD-only applies. */
    public function localCurrency(): ?string
    {
        return app(\App\Services\CustomerCurrencyService::class)->localCurrencyFor($this);
    }

    public function roleDisplayName(): string
    {
        return \App\Support\RoleRegistry::displayName($this->role);
    }

    public function hasPendingRoleRequest(): bool
    {
        return $this->role_approval_status === 'pending' && !empty($this->requested_role);
    }

    public function approver() { return $this->belongsTo(User::class, 'role_approved_by'); }

    /** Foreground glove preference: normalized viewport coords, safe defaults. */
    public function glovePreference(): array
    {
        $mode = in_array($this->glove_mode, ['moving', 'fixed'], true) ? $this->glove_mode : 'moving';
        $x = is_numeric($this->glove_x) ? (float) $this->glove_x : null;
        $y = is_numeric($this->glove_y) ? (float) $this->glove_y : null;
        if ($x === null || $y === null || !is_finite($x) || !is_finite($y)) {
            return ['mode' => 'moving', 'x' => null, 'y' => null];
        }
        return ['mode' => $mode, 'x' => min(0.92, max(0.08, $x)), 'y' => min(0.92, max(0.08, $y))];
    }

    public function accountApprover() { return $this->belongsTo(User::class, 'approved_by'); }

    public function company() { return $this->belongsTo(Company::class); }
    public function tickets() { return $this->hasMany(Ticket::class, 'customer_id'); }
    public function assignedTickets() { return $this->hasMany(Ticket::class, 'assigned_to'); }
    public function projects() { return $this->hasMany(Project::class, 'customer_id'); }
    public function managedProjects() { return $this->hasMany(Project::class, 'project_manager_id'); }
    public function invoices() { return $this->hasMany(Invoice::class, 'customer_id'); }
    public function serviceOrders() { return $this->hasMany(ServiceOrder::class, 'customer_id'); }
    public function payments() { return $this->hasMany(Payment::class, 'customer_id'); }
    public function commissions() { return $this->hasMany(Commission::class, 'worker_id'); }
    public function tasks() { return $this->hasMany(Task::class, 'assigned_to'); }
    public function createdTasks() { return $this->hasMany(Task::class, 'created_by'); }
    public function expenses() { return $this->hasMany(Expense::class, 'created_by'); }
    public function salaries() { return $this->hasMany(Salary::class); }
    public function auditLogs() { return $this->hasMany(AuditLog::class); }
    public function notificationPreferences() { return $this->hasMany(NotificationPreference::class); }
    public function salary() { return $this->hasOne(Salary::class); }
    // Member identity extensions (single identity; relations, never duplicate users).
    public function settings() { return $this->hasMany(UserSetting::class); }
    public function identityDocuments() { return $this->hasMany(IdentityDocument::class); }
    public function idCards() { return $this->hasMany(MemberIdCard::class); }
    public function activeIdCard() { return $this->hasOne(MemberIdCard::class)->where('status', 'active')->latestOfMany(); }
    public function sentMessages() { return $this->hasMany(DirectMessage::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(DirectMessage::class, 'recipient_id'); }
    public function emergencyRequests() { return $this->hasMany(EmergencyRequest::class, 'requester_id'); }
    public function bankTransfers() { return $this->hasMany(BankTransfer::class, 'beneficiary_id'); }
    public function franchise() { return $this->belongsTo(Franchise::class); }
    public function contributedTasks() { return $this->belongsToMany(Task::class, 'task_contributors', 'user_id', 'task_id')->withPivot('role')->withTimestamps(); }
    // Account-ecosystem extensions (single identity; relations, never duplicate users).
    public function profileDetail() { return $this->hasOne(ProfileDetail::class); }
    public function compensation() { return $this->hasOne(EmployeeCompensation::class); }
    public function assignments() { return $this->hasMany(EmployeeAssignment::class, 'employee_id'); }
    public function activeAssignments() { return $this->hasMany(EmployeeAssignment::class, 'employee_id')->where('status', 'active'); }
    public function callLogs() { return $this->hasMany(CallLog::class, 'caller_id'); }
    public function receivedCalls() { return $this->hasMany(CallLog::class, 'recipient_id'); }

    public function getAvatarUrlAttribute()
    {
        // Authorized delivery (private disk): never a predictable public URL.
        if ($this->avatar) {
            return route('avatar.show', $this->id);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff&bold=true';
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeStaff($query) { return $query->whereNotIn('role', ['customer', 'freelancer', 'commission_agent']); }
    public function scopeCustomers($query) { return $query->where('role', 'customer'); }

    /**
     * Enforce hashing at the model boundary so every account-creation path
     * (including future ones) stores a credential Laravel can authenticate.
     * Also mints the server-generated member number (never user-supplied).
     */
    public function setPasswordAttribute($value): void
    {
        $hashInfo = is_string($value) ? password_get_info($value) : null;

        $this->attributes['password'] = is_string($value) && empty($hashInfo['algo'])
            ? Hash::make($value)
            : $value;
    }

    protected static function booted(): void
    {
        // Server-generated sequential member number (PREFIX-<id>), assigned
        // after insert so it is unique without races. Never user-supplied.
        static::created(function (self $user) {
            if (empty($user->member_number)) {
                $prefix = [
                    'customer' => 'CUS', 'employee' => 'EMP', 'freelancer' => 'FRL',
                    'commission_agent' => 'AGT', 'sales_agent' => 'SAL',
                    'support_agent' => 'SUP', 'support_manager' => 'SUP',
                    'project_manager' => 'PMG', 'finance_manager' => 'FIN',
                    'training_manager' => 'TRN', 'admin' => 'ADM', 'super_admin' => 'ADM',
                ][$user->role] ?? 'MBR';
                $user->forceFill(['member_number' => $prefix . '-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT)])->saveQuietly();
            }
        });
    }

    // ── Presence (§18): explicit state + automatic expiry ──
    public const PRESENCES = ['online', 'away', 'busy', 'dnd', 'offline'];

    /**
     * Effective presence: explicit state expires to offline when the last
     * recorded activity is older than 5 minutes (never a stale "online").
     * Hidden entirely when the member disabled presence visibility
     * (owner + admins still resolve it server-side where required).
     */
    public function effectivePresence(): string
    {
        $state = in_array($this->presence, self::PRESENCES, true) ? $this->presence : 'offline';
        if ($state === 'offline') return 'offline';
        $last = $this->last_activity_at;
        if (!$last || $last->lt(now()->subMinutes(5))) return 'offline';
        return $state;
    }

    public function presenceLabel(): string
    {
        return ['online' => 'Available', 'away' => 'Away', 'busy' => 'Busy', 'dnd' => 'Do not disturb', 'offline' => 'Offline'][$this->effectivePresence()];
    }

    public function presenceDot(): string
    {
        return ['online' => 'bg-emerald-500', 'away' => 'bg-amber-400', 'busy' => 'bg-red-500', 'dnd' => 'bg-purple-500', 'offline' => 'bg-gray-400'][$this->effectivePresence()];
    }
    // ── Identity / verification state (distinct from 2FA) ──
    public function isIdentityVerified(): bool
    {
        return $this->identity_status === 'verified' && !is_null($this->identity_verified_at);
    }

    public function verificationSummary(): array
    {
        return [
            'account' => ($this->is_active ?? true) ? 'active' : 'inactive',
            'identity' => $this->identity_status,
            'two_factor' => $this->hasMfaEnabled() ? 'enabled' : 'disabled',
            'email' => $this->isEmailVerified() ? 'verified' : 'unverified',
            'phone' => $this->isPhoneVerified() ? 'verified' : 'unverified',
        ];
    }

    /** Consume one recovery code (one-time, hashed, never plaintext). */
    public function consumeRecoveryCode(string $code): bool
    {
        $hashes = $this->two_factor_recovery_codes ?? [];
        if (!is_array($hashes) || $hashes === []) return false;
        foreach ($hashes as $i => $hash) {
            if (\Illuminate\Support\Facades\Hash::check($code, $hash)) {
                unset($hashes[$i]);
                $this->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();
                return true;
            }
        }
        return false;
    }
}
