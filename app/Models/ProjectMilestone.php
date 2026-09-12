<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $fillable = ['project_id', 'name', 'description', 'due_date', 'is_completed', 'completed_at', 'sort_order'];
    public function project() { return $this->belongsTo(Project::class); }
}
