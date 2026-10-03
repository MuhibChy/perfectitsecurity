<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id', 'group', 'key', 'value', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Typed read with default. */
    public static function get(User $user, string $group, string $key, $default = null)
    {
        $row = static::where('user_id', $user->id)->where('group', $group)->where('key', $key)->first();
        if (! $row) {
            return $default;
        }

        return match ($row->type) {
            'bool' => (bool) $row->value,
            'int' => (int) $row->value,
            'json' => json_decode($row->value ?? 'null', true) ?? $default,
            default => $row->value,
        };
    }

    public static function set(User $user, string $group, string $key, $value, string $type = 'string'): self
    {
        $stored = $type === 'json' ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        return static::updateOrCreate(
            ['user_id' => $user->id, 'group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );
    }
}
