<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Member presence heartbeat. Explicit states (online/away/busy/dnd/offline)
 * expire automatically via User::effectivePresence() + last_activity_at —
 * a stale session is never shown as online. State changes are per-member
 * only (ownership from session) and privacy-respecting.
 */
class PresenceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'presence' => 'required|in:online,away,busy,dnd,offline',
            'presence_visible' => 'sometimes|boolean',
        ]);
        $user = $request->user();
        $user->forceFill([
            'presence' => $data['presence'],
            'last_activity_at' => now(),
            ...(array_key_exists('presence_visible', $data) ? ['presence_visible' => (bool) $data['presence_visible']] : []),
        ])->saveQuietly();

        return response()->json([
            'presence' => $user->effectivePresence(),
            'label' => $user->presenceLabel(),
        ]);
    }

    /** Batch presence for a conversation roster (viewer-authorized ids only). */
    public function roster(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|max:50', 'ids.*' => 'integer|exists:users,id']);
        $viewer = $request->user();
        $out = [];
        foreach (User::whereIn('id', $data['ids'])->get() as $u) {
            if ((int) $u->id === (int) $viewer->id || $viewer->isAdmin()) {
                $out[$u->id] = ['presence' => $u->effectivePresence(), 'label' => $u->presenceLabel()];
            } elseif (! $u->presence_visible) {
                $out[$u->id] = ['presence' => 'offline', 'label' => 'Offline'];
            } else {
                $out[$u->id] = ['presence' => $u->effectivePresence(), 'label' => $u->presenceLabel()];
            }
        }
        AuditLog::log('presence.roster', 'presence', null, "Presence roster viewed by {$viewer->name} (".count($out).' members).');

        return response()->json($out);
    }
}
