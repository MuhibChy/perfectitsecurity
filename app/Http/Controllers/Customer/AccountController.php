<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use App\Services\AccountEarningsService;
use App\Services\CallLogService;
use App\Services\ProfileCompletionService;
use App\Services\TraceabilityService;

/**
 * Customer lifelong financial account (§6, §29): aggregates from the
 * EXISTING order/invoice/payment/wallet rows — no duplicate records.
 * Service history reuses TraceabilityService; comms merge own messages +
 * customer-visible calls. Every query is scoped to auth()->user().
 */
class AccountController extends Controller
{
    public function summary(AccountEarningsService $earnings, ProfileCompletionService $completion)
    {
        $user = auth()->user();
        return view('customer.account.summary', [
            'user' => $user,
            'finance' => $earnings->forCustomer($user),
            'completion' => $completion->for($user),
            'verification' => $user->verificationSummary(),
        ]);
    }

    public function comms(CallLogService $calls)
    {
        $user = auth()->user();
        $messages = DirectMessage::where(fn ($w) => $w->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
            ->visibleTo($user)->with(['sender', 'recipient', 'related'])->latest()->paginate(20);
        $callLogs = $calls->forUser($user);
        $timeline = TraceabilityService::customerTimeline($user, 'portal', 30);
        return view('customer.account.comms', compact('messages', 'callLogs', 'timeline'));
    }
}
