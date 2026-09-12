<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    private const NOTIFICATION_TYPES = [
        'ticket_created' => 'Ticket Created',
        'ticket_updated' => 'Ticket Updated',
        'ticket_reply' => 'Ticket Reply',
        'invoice_created' => 'Invoice Created',
        'invoice_paid' => 'Invoice Paid',
        'invoice_overdue' => 'Invoice Overdue',
        'project_updated' => 'Project Updated',
        'project_completed' => 'Project Completed',
        'quotation_received' => 'Quotation Received',
        'quotation_approved' => 'Quotation Approved',
        'sla_warning' => 'SLA Warning',
        'sla_breach' => 'SLA Breach',
        'system_announcement' => 'System Announcements',
        'marketing' => 'Marketing & Promotions',
    ];

    public function index()
    {
        $user = auth()->user();
        $preferences = NotificationPreference::where('user_id', $user->id)->get()->keyBy('notification_type');
        $types = self::NOTIFICATION_TYPES;

        return view('customer.notifications.preferences', compact('preferences', 'types'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $allTypes = self::NOTIFICATION_TYPES;

        foreach ($allTypes as $type => $label) {
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id, 'notification_type' => $type],
                [
                    'email_enabled' => $request->boolean("email_{$type}"),
                    'in_app_enabled' => $request->boolean("in_app_{$type}"),
                ]
            );
        }

        return redirect()->route('portal.notifications.preferences')
            ->with('success', 'Notification preferences updated!');
    }
}
