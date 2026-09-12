<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'notification_type', 'email_enabled', 'in_app_enabled'];
    public function user() { return $this->belongsTo(User::class); }
}
