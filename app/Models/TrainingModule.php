<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingModule extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'sort_order'];

    public function course() { return $this->belongsTo(TrainingCourse::class, 'course_id'); }
    public function lessons() { return $this->hasMany(TrainingLesson::class, 'module_id')->orderBy('sort_order'); }
}
