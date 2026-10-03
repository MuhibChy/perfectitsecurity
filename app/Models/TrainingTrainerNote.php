<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingTrainerNote extends Model
{
    protected $fillable = ['user_id', 'author_id', 'note'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
