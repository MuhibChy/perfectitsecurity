<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Foreground glove preference for the authenticated user ONLY.
 * No user_id is ever accepted from the request (IDOR-proof by design):
 * every operation resolves ownership from the session user.
 */
class GlovePreferenceController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->glovePreference());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mode' => 'required|in:moving,fixed',
            'x' => 'nullable|numeric|min:0|max:1',
            'y' => 'nullable|numeric|min:0|max:1',
        ]);
        $x = isset($data['x']) ? (float) $data['x'] : null;
        $y = isset($data['y']) ? (float) $data['y'] : null;
        if (($x !== null && !is_finite($x)) || ($y !== null && !is_finite($y))) {
            abort(422, 'Invalid glove coordinates.');
        }
        // Fixed mode requires a valid position; clamp inside safe margins.
        if ($data['mode'] === 'fixed') {
            if ($x === null || $y === null) {
                abort(422, 'A fixed position requires coordinates.');
            }
            $x = min(0.92, max(0.08, $x));
            $y = min(0.92, max(0.08, $y));
        }

        $request->user()->update([
            'glove_mode' => $data['mode'],
            'glove_x' => $x,
            'glove_y' => $y,
        ]);

        return response()->json($request->user()->fresh()->glovePreference());
    }

    public function reset(Request $request)
    {
        $request->user()->update(['glove_mode' => 'moving', 'glove_x' => null, 'glove_y' => null]);
        return response()->json($request->user()->fresh()->glovePreference());
    }
}
