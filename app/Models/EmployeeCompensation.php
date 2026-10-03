<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compensation-model CONFIGURATION (salary / commission / project / hybrid).
 * Actual money movement stays in salaries / commissions /
 * commission_payouts / bank_transfers — this row only declares the model.
 */
class EmployeeCompensation extends Model
{
    // "compensation" is uncountable — Laravel would resolve employee_compensation.
    protected $table = 'employee_compensations';

    protected $fillable = [
        'user_id', 'has_salary', 'salary_amount', 'salary_frequency', 'salary_start_date',
        'has_commission', 'commission_type', 'commission_value', 'commission_rule_id',
        'has_project_pay', 'project_terms', 'status', 'updated_by',
    ];

    protected $casts = [
        'has_salary' => 'boolean',
        'has_commission' => 'boolean',
        'has_project_pay' => 'boolean',
        'salary_start_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function rule() { return $this->belongsTo(CommissionRule::class, 'commission_rule_id'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }

    /** Human-readable model label, e.g. "Salary + Commission". */
    public function modelLabel(): string
    {
        $parts = [];
        if ($this->has_salary) $parts[] = 'Salary';
        if ($this->has_commission) $parts[] = 'Commission';
        if ($this->has_project_pay) $parts[] = 'Project';
        return $parts === [] ? 'Not configured' : implode(' + ', $parts);
    }
}
