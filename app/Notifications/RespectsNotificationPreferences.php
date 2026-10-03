<?php

namespace App\Notifications;

use App\Models\NotificationPreference;

/**
 * Preference-aware channels (Phase 9). No preference row → both channels
 * (backward compatible). A row disabling a channel suppresses it.
 * Emergency/critical flows pass force via ServiceTrackingService::notify(..., true).
 */
trait RespectsNotificationPreferences
{
    abstract protected function preferenceType(): string;

    /** Channel list honoring the recipient's stored preferences. */
    public function preferenceChannels(object $notifiable): array
    {
        $channels = [];
        $pref = NotificationPreference::where('user_id', $notifiable->id ?? $notifiable->getKey())
            ->where('notification_type', $this->preferenceType())
            ->first();
        if (! $pref || (bool) $pref->in_app_enabled) {
            $channels[] = 'database';
        }
        if (! $pref || (bool) $pref->email_enabled) {
            $channels[] = 'mail';
        }

        return $channels ?: ['database'];
    }
}
