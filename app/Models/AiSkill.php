<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSkill extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'system_instructions',
        'trigger_keywords', 'allowed_roles', 'priority', 'status',
        'version', 'category', 'temperature', 'max_tokens',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'trigger_keywords' => 'array',
        'allowed_roles' => 'array',
        'temperature' => 'float',
    ];

    public function scopeEnabled($q) { return $q->where('status', 'enabled'); }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }

    public function isEnabled(): bool { return $this->status === 'enabled'; }

    /** Role gate: empty allowed_roles = every role (guests included). */
    public function allowsRole(?User $user): bool
    {
        $roles = $this->allowed_roles ?? [];
        if (empty($roles)) return true;
        if (!$user) return in_array('guest', $roles, true);
        return in_array($user->role, $roles, true);
    }

    /**
     * Keyword match score for a message (case-insensitive substring hits).
     */
    public function matchScore(string $message): int
    {
        $score = 0;
        $lower = mb_strtolower($message);
        foreach ($this->trigger_keywords ?? [] as $keyword) {
            $keyword = trim(mb_strtolower((string) $keyword));
            if ($keyword !== '' && str_contains($lower, $keyword)) {
                $score++;
            }
        }
        return $score;
    }
}
