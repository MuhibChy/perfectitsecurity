<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'name', 'email', 'comment', 'is_approved'];
    protected $casts = ['is_approved' => 'boolean'];
    public function post() { return $this->belongsTo(BlogPost::class, 'post_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
