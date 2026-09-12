<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'project_number', 'name', 'slug', 'description', 'customer_id',
        'project_manager_id', 'service_id', 'budget', 'estimated_cost',
        'actual_cost', 'start_date', 'deadline', 'completed_at', 'progress',
        'status', 'priority',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'project_manager_id' => 'integer',
        'service_id' => 'integer',
        'budget' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'progress' => 'integer',
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function projectManager() { return $this->belongsTo(User::class, 'project_manager_id'); }
    public function service() { return $this->belongsTo(Service::class); }
    public function members() { return $this->belongsToMany(User::class, 'project_members')->withPivot('role'); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function milestones() { return $this->hasMany(ProjectMilestone::class); }
    public function files() { return $this->hasMany(ProjectFile::class); }
    public function comments() { return $this->hasMany(ProjectComment::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }

    public function scopeActive($query) { return $query->whereIn('status', ['planning', 'in_progress', 'on_hold', 'review']); }
    public function scopeForCustomer($query, $id) { return $query->where('customer_id', $id); }

    public function getBudgetUsedPercentAttribute()
    {
        return $this->budget > 0 ? round(($this->actual_cost / $this->budget) * 100, 1) : 0;
    }

    public function getProfitAttribute()
    {
        $revenue = $this->invoices()->where('status', 'paid')->sum('total');
        return $revenue - $this->actual_cost;
    }
}
