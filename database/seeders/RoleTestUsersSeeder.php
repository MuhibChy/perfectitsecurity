<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Quotation;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * RoleTestUsersSeeder — synthetic test accounts for every role plus a
 * minimal connected workflow per role (all amounts synthetic, all records
 * flagged is_demo where the model supports it).
 *
 * Credentials: password is read from TEST_SEED_PASSWORD and never stored
 * in source. Without it, accounts get an unusable random password and the
 * admin must set credentials via the normal reset flow (non-production).
 */
class RoleTestUsersSeeder extends Seeder
{
    public const DOMAIN = 'example.test';

    public function run(): void
    {
        $password = env('TEST_SEED_PASSWORD');
        $make = function (string $name, string $local, string $role, string $purpose) use ($password) {
            return User::updateOrCreate(
                ['email' => $local.'@'.self::DOMAIN],
                [
                    'name' => $name, 'role' => $role, 'is_active' => true,
                    'is_demo' => true,
                    'email_verified_at' => now(),
                    'password' => $password ? Hash::make($password) : Hash::make(Str::random(32)),
                ]
            );
        };

        $customer = $make('Customer Test User', 'customer.test', 'customer', 'customer journey');
        $support = $make('Support Agent Test User', 'support.test', 'support_agent', 'tickets');
        $pm = $make('Project Manager Test User', 'project.test', 'project_manager', 'projects');
        $finance = $make('Finance Manager Test User', 'finance.test', 'finance_manager', 'finance');
        $engineer = $make('Employee Engineer Test User', 'engineer.test', 'employee', 'tasks');
        $contractor = $make('Freelancer Contractor Test User', 'freelancer.test', 'freelancer', 'contractor work');
        $admin = $make('System Administrator Test User', 'admin.test', 'admin', 'administration');

        // Customer chain: ticket + request-linked project/task + quote/invoice/payment.
        $ticket = Ticket::firstOrCreate(
            ['ticket_number' => 'TK-TEST-0001'],
            ['customer_id' => $customer->id, 'subject' => '[TEST] Email outage for 5 users',
                'description' => 'Synthetic test ticket: mailbox sync failure reported by the test customer.',
                'priority' => 'high', 'status' => 'assigned', 'assigned_to' => $support->id]
        );
        TicketMessage::firstOrCreate(
            ['ticket_id' => $ticket->id, 'user_id' => $support->id, 'message' => 'Synthetic agent response: investigating mailbox sync.'],
            ['is_internal_note' => false]
        );

        $project = Project::firstOrCreate(
            ['project_number' => 'PRJ-TEST-0001'],
            ['name' => '[TEST] NorthBridge Website Security Assessment', 'slug' => 'test-northbridge-assessment',
                'customer_id' => $customer->id, 'project_manager_id' => $pm->id,
                'status' => 'in_progress', 'deadline' => now()->addWeeks(3)->toDateString()]
        );
        ProjectMilestone::firstOrCreate(
            ['project_id' => $project->id, 'name' => '[TEST] Assessment complete'],
            ['due_date' => now()->addWeeks(2)->toDateString()]
        );
        Task::firstOrCreate(
            ['task_number' => 'TSK-TEST-0001'],
            ['project_id' => $project->id, 'title' => '[TEST] Authorized security testing',
                'assigned_to' => $engineer->id, 'created_by' => $pm->id, 'status' => 'in_progress']
        );
        Task::firstOrCreate(
            ['task_number' => 'TSK-TEST-0002'],
            ['project_id' => $project->id, 'title' => '[TEST] Findings report draft',
                'assigned_to' => $contractor->id, 'created_by' => $pm->id, 'status' => 'pending']
        );

        $quotation = Quotation::firstOrCreate(
            ['customer_id' => $customer->id, 'total' => 1000],
            ['notes' => '[TEST] Managed IT Support quotation.', 'terms' => 'Synthetic test terms.',
                'subtotal' => 1000, 'tax_rate' => 0, 'tax_amount' => 0, 'status' => 'accepted',
                'valid_until' => now()->addDays(30), 'accepted_at' => now()]
        );
        $invoice = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-TEST-0001'],
            ['customer_id' => $customer->id, 'project_id' => $project->id,
                'subtotal' => 1000, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 1000,
                'amount_paid' => 400, 'amount_due' => 600, 'status' => 'partially_paid',
                'due_date' => now()->addDays(14)]
        );
        Payment::firstOrCreate(
            ['payment_number' => 'PAY-TEST-0001'],
            ['invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 400,
                'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]
        );
        Commission::firstOrCreate(
            ['commission_number' => 'COM-TEST-0001'],
            ['worker_id' => $contractor->id, 'customer_id' => $customer->id, 'project_id' => $project->id,
                'revenue_amount' => 1000, 'commission_rate' => 5, 'commission_amount' => 50, 'status' => 'pending']
        );

        $this->command?->info('RoleTestUsersSeeder: 7 test accounts (@'.self::DOMAIN.') with linked test workflow ensured.');
    }
}
