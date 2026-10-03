<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EmployeeAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Structured employee ↔ work assignments (§15). History rows are
 * transitioned, never overwritten: completing/revoking stamps the existing
 * row; re-assignment creates a new row (full audit trail via AuditLog).
 */
class AssignmentService
{
    /** Morph allow-list: only real business-work targets, never arbitrary models. */
    public const ASSIGNABLE = [
        'customer' => User::class,
        'project' => \App\Models\Project::class,
        'order' => \App\Models\ServiceOrder::class,
        'service' => \App\Models\Service::class,
        'ticket' => \App\Models\Ticket::class,
        'task' => \App\Models\Task::class,
    ];

    public function assign(User $actor, User $employee, string $type, int $id, ?string $notes = null): EmployeeAssignment
    {
        abort_unless(isset(self::ASSIGNABLE[$type]), 422, 'Unknown assignment target.');
        $model = self::ASSIGNABLE[$type]::findOrFail($id);

        // Guard: customers may only ever be linked to themselves, never assigned as staff.
        if ($type === 'customer') {
            abort_unless($model->isCustomer(), 422, 'Only customer accounts can be assignment targets.');
        }
        abort_if($employee->isCustomer(), 422, 'Customers cannot receive staff assignments.');

        return DB::transaction(function () use ($actor, $employee, $type, $model, $notes) {
            // Close any identical active assignment first (history preserved).
            EmployeeAssignment::where('employee_id', $employee->id)
                ->where('assignable_type', self::ASSIGNABLE[$type])
                ->where('assignable_id', $model->getKey())
                ->active()->update(['status' => 'completed', 'completed_at' => now()]);

            $assignment = EmployeeAssignment::create([
                'employee_id' => $employee->id,
                'assignable_type' => self::ASSIGNABLE[$type],
                'assignable_id' => $model->getKey(),
                'assigned_by' => $actor->id,
                'status' => 'active',
                'started_at' => now(),
                'notes' => $notes,
            ]);
            AuditLog::log('assignment.created', 'employee_assignments', $assignment, "{$actor->name} assigned {$employee->name} to {$type} #{$model->getKey()}.");
            ServiceTrackingService::notify((int) $employee->id, 'assignment_created', 'New assignment', "You were assigned to {$type} #{$model->getKey()} by {$actor->name}.");
            return $assignment->fresh();
        });
    }

    public function transition(User $actor, EmployeeAssignment $assignment, string $status): EmployeeAssignment
    {
        abort_unless(in_array($status, ['completed', 'revoked'], true), 422, 'Invalid assignment status.');
        $assignment->update(['status' => $status, 'completed_at' => now()]);
        AuditLog::log('assignment.' . $status, 'employee_assignments', $assignment, "{$actor->name} marked assignment #{$assignment->id} {$status}.");
        return $assignment->fresh();
    }
}
