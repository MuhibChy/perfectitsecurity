<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Storage;

/**
 * Authorized profile-photo delivery. Files stay on the private disk;
 * visibility follows the owner's setting: open (any signed-in member),
 * contacts (shared ticket/project/task/message thread), or hidden
 * (owner + admins). Every denied access is a 404 (no existence oracle).
 */
class AvatarController extends Controller
{
    public function show(User $user)
    {
        $viewer = auth()->user();
        abort_unless($viewer, 401);
        abort_unless($user->avatar && Storage::disk('private')->exists($user->avatar), 404);

        $visibility = UserSetting::get($user, 'privacy', 'avatar_visibility', $user->isStaff() ? 'open' : 'contacts');
        $allowed = (int) $viewer->id === (int) $user->id
            || $viewer->isAdmin()
            || $visibility === 'open'
            || ($visibility === 'contacts' && $this->sharesThread($viewer, $user));
        abort_unless($allowed, 404);

        $path = Storage::disk('private')->path($user->avatar);

        return response()->file($path, ['Cache-Control' => 'private, max-age=3600']);
    }

    protected function sharesThread(User $viewer, User $target): bool
    {
        if ($viewer->isStaff() || $target->isStaff()) {
            $sharedTicket = \App\Models\Ticket::where('customer_id', $viewer->isCustomer() ? $viewer->id : $target->id)
                ->where('assigned_to', $viewer->isCustomer() ? $target->id : $viewer->id)->exists();
            if ($sharedTicket) {
                return true;
            }
            $sharedTask = \App\Models\Task::where(function ($q) use ($viewer, $target) {
                $q->where('assigned_to', $viewer->id)->where('customer_id', $target->id);
            })->orWhere(function ($q) use ($viewer, $target) {
                $q->where('assigned_to', $target->id)->where('customer_id', $viewer->id);
            })->exists();
            if ($sharedTask) {
                return true;
            }
        }

        return \App\Models\DirectMessage::where(function ($q) use ($viewer, $target) {
            $q->where('sender_id', $viewer->id)->where('recipient_id', $target->id);
        })->orWhere(function ($q) use ($viewer, $target) {
            $q->where('sender_id', $target->id)->where('recipient_id', $viewer->id);
        })->exists();
    }
}
