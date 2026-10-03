<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = ['salary_number', 'user_id', 'base_salary', 'bonus', 'deductions', 'net_salary', 'period', 'pay_date', 'status', 'notes', 'effective_from', 'effective_to', 'currency', 'approved_by', 'approved_at'];
    protected $casts = ['pay_date' => 'date', 'effective_from' => 'date', 'effective_to' => 'date', 'approved_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    protected static function booted(): void
    {
        // Unique payroll reference (§29); backfilled rows keep SAL-<id>.
        static::creating(function (self $salary) {
            if (empty($salary->salary_number)) {
                $salary->salary_number = 'SAL-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }
}
