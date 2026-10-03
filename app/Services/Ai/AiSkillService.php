<?php

namespace App\Services\Ai;

use App\Models\AiSkill;
use App\Models\User;

/**
 * Skill detection: which approved behavior definition applies to a message.
 * Detection is deterministic keyword scoring over admin-managed skills —
 * the model never chooses its own instructions.
 */
class AiSkillService
{
    /**
     * Best matching enabled skill for this user, or null.
     * Highest keyword score wins; skill priority breaks ties.
     */
    public function detectSkill(string $message, ?User $user): ?AiSkill
    {
        $best = null;
        $bestScore = 0;
        foreach (AiSkill::enabled()->orderBy('priority')->get() as $skill) {
            if (!$skill->allowsRole($user)) continue;
            $score = $skill->matchScore($message);
            if ($score > $bestScore) {
                $best = $skill;
                $bestScore = $score;
            }
        }
        return $best;
    }

    public function activeSkills(?User $user): \Illuminate\Support\Collection
    {
        return AiSkill::enabled()->orderBy('priority')->get()
            ->filter(fn (AiSkill $s) => $s->allowsRole($user))->values();
    }
}
