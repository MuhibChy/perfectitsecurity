<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\TraceabilityService;

/**
 * Customer self-service history: own records only, scoped to auth()->user().
 * Internal notes, audit detail and other customers are never exposed.
 */
class HistoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $overview = TraceabilityService::customerOverview($user);
        $timeline = TraceabilityService::customerTimeline($user, 'portal', 100);
        return view('customer.history.index', compact('overview', 'timeline'));
    }
}
