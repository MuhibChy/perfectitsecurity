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
        'company_name', 'address', 'city', 'state', 'zip_code',
        'country', 'preferred_currency', 'preferred_locale', 'stripe_customer_id',
        'is_active', 'two_factor_enabled', 'two_factor_secret', 'two_factor_confirmed_at',
        'last_login_at', 'last_login_ip', 'company_id', 'email_verified_at', 'phone_verified_at', 'verification_status',
        'email_otp_hash', 'email_otp_expires_at', 'email_otp_attempts', 'email_otp_sent_at',
        'phone_otp_hash', 'phone_otp_expires_at', 'phone_otp_attempts', 'phone_otp_sent_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'phone_otp_hash', 'email_otp_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_otp_expires_at' => 'datetime',
        'phone_otp_sent_at' => 'datetime',
        'phone_otp_attempts' => 'integer',
        'last_login_at' => 'datetime',
        'email_otp_hash' => 'string',
        'email_otp_expires_at' => 'datetime',
        'email_otp_attempts' => 'integer',
        'email_otp_sent_at' => 'datetime',
        'password' => 'string',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
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

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff&bold=true';
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeStaff($query) { return $query->whereNotIn('role', ['customer', 'freelancer', 'commission_agent']); }
    public function scopeCustomers($query) { return $query->where('role', 'customer'); }

    /**
     * Enforce hashing at the model boundary so every account-creation path
     * (including future ones) stores a credential Laravel can authenticate.
     */
    public function setPasswordAttribute($value): void
    {
        $hashInfo = is_string($value) ? password_get_info($value) : null;

        $this->attributes['password'] = is_string($value) && empty($hashInfo['algo'])
            ? Hash::make($value)
            : $value;
    }
}
