<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = ['user_id', 'base_salary', 'bonus', 'deductions', 'net_salary', 'period', 'pay_date', 'status', 'notes'];
    public function user() { return $this->belongsTo(User::class); }
}
