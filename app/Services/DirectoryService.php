<?php

namespace App\Services;

use App\Models\EmployeeAssignment;
use App\Models\Project;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\User;

/**
 * Privacy-aware contact directory (§11, §18). Two card shapes:
 * - publicCard: safe for any authenticated viewer (name, role, department,
 *   avatar, presence when visible, preferred method, hours).
 * - direct contact (phone/whatsapp/email) only when a working relationship
 *   exists (active assignment, shared ticket/project/order) or the viewer
 *   is staff/admin. Internal notes, compensation and emergency contacts are
 *   NEVER included here (admin 360° only).
 */
class DirectoryService
{
    public function staffCards(User $viewer)
    {
        return User::where('is_active', true)
            ->whereNotIn('role', ['customer'])
            ->orderBy('name')
            ->paginate(20);
    }

    public function customerCards(User $viewer)
    {
        abort_unless($viewer->isStaff() || $viewer->isFreelancer(), 403);
        $q = User::where('role', 'customer')->where('is_active', true)->orderBy('name');
        if ($viewer->isFreelancer()) {
            // Contractors see only customers linked to their own work.
            $ids = $this->linkedCustomerIds($viewer);
            $q->whereIn('id', $ids);
        }

        return $q->paginate(20);
    }

    public function publicCard(User $staff): array
    {
        $staff->loadMissing('profileDetail');
        $d = $staff->profileDetail;

        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'role' => $staff->roleDisplayName(),
            'job_title' => $staff->job_title,
            'department' => $staff->department,
            'avatar_url' => $staff->avatar_url,
            'presence' => $staff->presence_visible ? $staff->presenceLabel() : null,
            'preferred_contact_method' => $d?->preferred_contact_method,
            'contact_hours' => $d?->contact_hours,
            'skills' => $d?->skills ?? [],
        ];
    }

    /** Direct phone/whatsapp/email only with a working relationship (or staff viewer). */
    public function canSeeDirectContact(User $viewer, User $staff): bool
    {
        if ((int) $viewer->id === (int) $staff->id) {
            return true;
        }
        if ($viewer->isStaff()) {
            return true;
        }
        // Any working relationship unlocks direct contact: a direct
        // assignment to this customer, or a shared ticket/project/order.
        return $this->linkedToStaff($viewer, $staff);
    }

    public function directContact(User $staff): array
    {
        return ['email' => $staff->email, 'phone' => $staff->phone, 'whatsapp' => $staff->profileDetail?->whatsapp_number];
    }

    protected function linkedToStaff(User $customer, User $staff): bool
    {
        if (EmployeeAssignment::where('employee_id', $staff->id)->active()
            ->where('assignable_type', User::class)->where('assignable_id', $customer->id)->exists()) {
            return true;
        }
        if (Ticket::where('customer_id', $customer->id)->where('assigned_to', $staff->id)->exists()) {
            return true;
        }
        if (Project::where('customer_id', $customer->id)->where(function ($q) use ($staff) {
            $q->where('project_manager_id', $staff->id)->orWhereHas('members', fn ($m) => $m->where('user_id', $staff->id));
        })->exists()) {
            return true;
        }
        if (ServiceOrder::where('customer_id', $customer->id)->where('assigned_to', $staff->id)->exists()) {
            return true;
        }

        return false;
    }

    protected function linkedCustomerIds(User $contractor): array
    {
        $taskIds = \App\Models\Task::where('assigned_to', $contractor->id)->pluck('id')
            ->merge(\App\Models\TaskContributor::where('user_id', $contractor->id)->pluck('task_id'))->unique();
        $tasks = \App\Models\Task::with(['project.customer', 'customer', 'serviceOrder.customer'])->whereIn('id', $taskIds)->get();
        $ids = [];
        foreach ($tasks as $t) {
            $c = $t->project?->customer ?? $t->customer ?? $t->serviceOrder?->customer;
            if ($c) {
                $ids[] = $c->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
