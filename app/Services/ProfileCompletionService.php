<?php

namespace App\Services;

use App\Models\User;

/**
 * Profile-completeness scoring (§2). Core items mirror the required list;
 * extended items come from profile_details. Never writes — read-only.
 */
class ProfileCompletionService
{
    public function for(User $user): array
    {
        $user->loadMissing('profileDetail');
        $d = $user->profileDetail;

        $items = [
            'Profile picture' => !empty($user->avatar),
            'Full name' => !empty($user->name),
            'Valid email' => !empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL),
            'Contact number' => !empty($user->phone),
            'Address' => !empty($user->address),
            'Country' => !empty($user->country),
            'Preferred communication method' => !empty($d?->preferred_contact_method),
        ];

        $missing = array_keys(array_filter($items, fn ($done) => !$done));
        $done = count($items) - count($missing);
        $percent = (int) round($done / max(count($items), 1) * 100);

        return ['percent' => $percent, 'missing' => $missing, 'items' => $items];
    }
}
