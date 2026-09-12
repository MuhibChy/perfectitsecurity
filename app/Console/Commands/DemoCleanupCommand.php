<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * demo:cleanup — removes ONLY sample records flagged is_demo=true.
 *
 * - Display names are realistic; identification is via the hidden
 *   is_demo boolean plus operational markers that are never
 *   customer-visible (demo.*@example.test emails, DEMO-TXN refs,
 *   type=demo notifications, documents/demo-* paths).
 * - Default mode is a dry run that lists what WOULD be deleted.
 * - Pass --confirm to actually delete.
 * - Refuses to run in production (APP_ENV=production).
 * - Real records are never matched.
 */
class DemoCleanupCommand extends Command
{
    protected $signature = 'demo:cleanup {--confirm : Actually delete the sample records (default is dry-run preview)}';
    protected $description = 'Preview or remove is_demo-flagged sample records (never touches real data)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run demo:cleanup in the production environment.');
            return self::FAILURE;
        }

        $plan = $this->buildPlan();
        $total = array_sum(array_column($plan, 'count'));

        $this->info($this->option('confirm') ? 'Deleting sample records…' : 'DRY RUN — nothing will be deleted. Pass --confirm to delete.');
        $this->table(['Entity', 'Sample rows', 'Matcher'], array_map(
            fn ($row) => [$row['entity'], $row['count'], $row['matcher']],
            $plan
        ));
        $this->info("Total sample rows: {$total}");

        if (!$this->option('confirm')) {
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $this->deletePlan();
        });

        // Remove synthetic sample files from private storage.
        foreach (Storage::disk('private')->files('documents') as $file) {
            if (str_starts_with(basename($file), 'demo-document-')) {
                Storage::disk('private')->delete($file);
            }
        }

        $this->info('Sample cleanup complete. Real records untouched.');
        return self::SUCCESS;
    }

    private function demo(string $table): \Illuminate\Database\Query\Builder
    {
        return DB::table($table)->where('is_demo', true);
    }

    private function demoUserIds(): array
    {
        return DB::table('users')->where('is_demo', true)->pluck('id')->all();
    }

    private function buildPlan(): array
    {
        $count = fn (string $table) => $this->demo($table)->count();
        $like = fn (string $table, string $col, string $pattern, string $label) => [
            'entity' => $table, 'matcher' => $label,
            'count' => DB::table($table)->where($col, 'like', $pattern)->count(),
        ];

        $invoiceIds = DB::table('invoices')->where('is_demo', true)->pluck('id')->all();
        $proposalIds = DB::table('proposals')->where('is_demo', true)->pluck('id')->all();
        $projectIds = DB::table('projects')->where('is_demo', true)->pluck('id')->all();
        $taskIds = DB::table('tasks')->where('is_demo', true)->pluck('id')->all();
        $ticketIds = DB::table('tickets')->where('is_demo', true)->pluck('id')->all();
        $quoteIds = DB::table('quotations')->where('is_demo', true)->pluck('id')->all();
        $leadIds = DB::table('leads')->where('is_demo', true)->pluck('id')->all();
        $kbIds = DB::table('kb_articles')->where('is_demo', true)->pluck('id')->all();
        $serviceIds = DB::table('services')->where('is_demo', true)->pluck('id')->all();
        $orderIds = DB::table('service_orders')->where('is_demo', true)->pluck('id')->all();

        return [
            ['entity' => 'payments', 'matcher' => 'is_demo', 'count' => $count('payments')],
            ['entity' => 'financial_transactions', 'matcher' => 'is_demo', 'count' => $count('financial_transactions')],
            ['entity' => 'invoice_items', 'matcher' => 'sample invoice ids', 'count' => empty($invoiceIds) ? 0 : DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->count()],
            ['entity' => 'invoices', 'matcher' => 'is_demo', 'count' => count($invoiceIds)],
            ['entity' => 'proposal_versions', 'matcher' => 'sample proposal ids', 'count' => empty($proposalIds) ? 0 : DB::table('proposal_versions')->whereIn('proposal_id', $proposalIds)->count()],
            ['entity' => 'proposal_sections', 'matcher' => 'sample proposal ids', 'count' => empty($proposalIds) ? 0 : DB::table('proposal_sections')->whereIn('proposal_id', $proposalIds)->count()],
            ['entity' => 'proposals', 'matcher' => 'is_demo', 'count' => count($proposalIds)],
            ['entity' => 'contracts', 'matcher' => 'is_demo', 'count' => $count('contracts')],
            ['entity' => 'service_orders', 'matcher' => 'is_demo', 'count' => count($orderIds)],
            ['entity' => 'task_applications/comments/attachments', 'matcher' => 'sample task ids', 'count' =>
                (empty($taskIds) ? 0 : DB::table('task_applications')->whereIn('task_id', $taskIds)->count())
                + (empty($taskIds) ? 0 : DB::table('task_comments')->whereIn('task_id', $taskIds)->count())
                + (empty($taskIds) ? 0 : DB::table('task_attachments')->whereIn('task_id', $taskIds)->count())],
            ['entity' => 'tasks', 'matcher' => 'is_demo', 'count' => count($taskIds)],
            ['entity' => 'project_milestones', 'matcher' => 'sample project ids', 'count' => empty($projectIds) ? 0 : DB::table('project_milestones')->whereIn('project_id', $projectIds)->count()],
            ['entity' => 'projects', 'matcher' => 'is_demo', 'count' => count($projectIds)],
            ['entity' => 'ticket_messages/time/attachments', 'matcher' => 'sample ticket ids', 'count' =>
                (empty($ticketIds) ? 0 : DB::table('ticket_messages')->whereIn('ticket_id', $ticketIds)->count())
                + (empty($ticketIds) ? 0 : DB::table('ticket_time_entries')->whereIn('ticket_id', $ticketIds)->count())
                + (empty($ticketIds) ? 0 : DB::table('ticket_attachments')->whereIn('ticket_id', $ticketIds)->count())],
            ['entity' => 'tickets', 'matcher' => 'is_demo', 'count' => count($ticketIds)],
            ['entity' => 'quotation_items', 'matcher' => 'sample quotation ids', 'count' => empty($quoteIds) ? 0 : DB::table('quotation_items')->whereIn('quotation_id', $quoteIds)->count()],
            ['entity' => 'quotations', 'matcher' => 'is_demo', 'count' => count($quoteIds)],
            ['entity' => 'service_requests', 'matcher' => 'is_demo', 'count' => $count('service_requests')],
            ['entity' => 'lead_activities', 'matcher' => 'sample lead ids', 'count' => empty($leadIds) ? 0 : DB::table('lead_activities')->whereIn('lead_id', $leadIds)->count()],
            ['entity' => 'leads', 'matcher' => 'is_demo', 'count' => count($leadIds)],
            ['entity' => 'kb_article_versions/votes', 'matcher' => 'sample article ids', 'count' =>
                (empty($kbIds) ? 0 : DB::table('kb_article_versions')->whereIn('article_id', $kbIds)->count())
                + (empty($kbIds) ? 0 : DB::table('kb_article_votes')->whereIn('article_id', $kbIds)->count())],
            ['entity' => 'kb_article_tag (pivot)', 'matcher' => 'sample article ids', 'count' => empty($kbIds) ? 0 : DB::table('kb_article_tag')->whereIn('article_id', $kbIds)->count()],
            ['entity' => 'kb_articles', 'matcher' => 'is_demo', 'count' => count($kbIds)],
            ['entity' => 'customer_documents', 'matcher' => 'is_demo (+ documents/demo-* files)', 'count' => $count('customer_documents')],
            ['entity' => 'notifications', 'matcher' => "is_demo (type='demo')", 'count' => $count('notifications')],
            ['entity' => 'service_country_prices', 'matcher' => 'sample service ids', 'count' => empty($serviceIds) ? 0 : DB::table('service_country_prices')->whereIn('service_id', $serviceIds)->count()],
            ['entity' => 'services', 'matcher' => 'is_demo', 'count' => count($serviceIds)],
            ['entity' => 'companies', 'matcher' => 'is_demo', 'count' => $count('companies')],
            ['entity' => 'expenses', 'matcher' => 'is_demo', 'count' => $count('expenses')],
            ['entity' => 'portfolio_items', 'matcher' => 'is_demo', 'count' => $count('portfolio_items')],
            ['entity' => 'case_studies', 'matcher' => 'is_demo', 'count' => $count('case_studies')],
            ['entity' => 'users', 'matcher' => 'is_demo (demo.*@example.test)', 'count' => count($this->demoUserIds())],
            $like('payments', 'transaction_id', 'DEMO-%', "legacy transaction_id LIKE 'DEMO-%'"),
            $like('customer_documents', 'path', 'documents/demo-%', "legacy path LIKE 'documents/demo-%'"),
            $like('notifications', 'type', 'demo', "legacy type='demo'"),
            $like('users', 'email', 'demo.%@example.test', "legacy demo.*@example.test"),
        ];
    }

    private function deletePlan(): void
    {
        $ids = fn (string $table) => DB::table($table)->where('is_demo', true)->pluck('id')->all();

        $userIds = $ids('users');
        $invoiceIds = $ids('invoices');
        $proposalIds = $ids('proposals');
        $orderIds = $ids('service_orders');
        $projectIds = $ids('projects');
        $taskIds = $ids('tasks');
        $ticketIds = $ids('tickets');
        $quoteIds = $ids('quotations');
        $leadIds = $ids('leads');
        $kbIds = $ids('kb_articles');
        $serviceIds = $ids('services');

        DB::table('payments')->where('is_demo', true)->delete();
        DB::table('financial_transactions')->where('is_demo', true)->delete();
        if ($invoiceIds) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
            DB::table('invoices')->whereIn('id', $invoiceIds)->delete();
        }
        if ($proposalIds) {
            DB::table('proposal_versions')->whereIn('proposal_id', $proposalIds)->delete();
            DB::table('proposal_sections')->whereIn('proposal_id', $proposalIds)->delete();
            DB::table('proposals')->whereIn('id', $proposalIds)->delete();
        }
        DB::table('contracts')->where('is_demo', true)->delete();
        if ($orderIds) {
            DB::table('service_orders')->whereIn('id', $orderIds)->delete();
        }
        if ($taskIds) {
            DB::table('task_applications')->whereIn('task_id', $taskIds)->delete();
            DB::table('task_comments')->whereIn('task_id', $taskIds)->delete();
            DB::table('task_attachments')->whereIn('task_id', $taskIds)->delete();
            DB::table('tasks')->whereIn('id', $taskIds)->delete();
        }
        if ($projectIds) {
            DB::table('project_milestones')->whereIn('project_id', $projectIds)->delete();
            DB::table('project_members')->whereIn('project_id', $projectIds)->delete();
            DB::table('projects')->whereIn('id', $projectIds)->delete();
        }
        if ($ticketIds) {
            DB::table('ticket_messages')->whereIn('ticket_id', $ticketIds)->delete();
            DB::table('ticket_time_entries')->whereIn('ticket_id', $ticketIds)->delete();
            DB::table('ticket_attachments')->whereIn('ticket_id', $ticketIds)->delete();
            DB::table('tickets')->whereIn('id', $ticketIds)->delete();
        }
        if ($quoteIds) {
            DB::table('quotation_items')->whereIn('quotation_id', $quoteIds)->delete();
            DB::table('quotations')->whereIn('id', $quoteIds)->delete();
        }
        DB::table('service_requests')->where('is_demo', true)->delete();
        if ($leadIds) {
            DB::table('lead_activities')->whereIn('lead_id', $leadIds)->delete();
            DB::table('leads')->whereIn('id', $leadIds)->delete();
        }
        if ($kbIds) {
            DB::table('kb_article_versions')->whereIn('article_id', $kbIds)->delete();
            DB::table('kb_article_votes')->whereIn('article_id', $kbIds)->delete();
            DB::table('kb_article_tag')->whereIn('article_id', $kbIds)->delete();
            DB::table('kb_articles')->whereIn('id', $kbIds)->delete();
        }
        DB::table('customer_documents')->where('is_demo', true)->delete();
        DB::table('notifications')->where('is_demo', true)->delete();
        if ($serviceIds) {
            DB::table('service_country_prices')->whereIn('service_id', $serviceIds)->delete();
            DB::table('services')->whereIn('id', $serviceIds)->delete();
        }
        DB::table('companies')->where('is_demo', true)->delete();
        DB::table('expenses')->where('is_demo', true)->delete();
        DB::table('portfolio_items')->where('is_demo', true)->delete();
        DB::table('case_studies')->where('is_demo', true)->delete();
        if ($userIds) {
            DB::table('users')->whereIn('id', $userIds)->update(['company_id' => null]);
            DB::table('users')->whereIn('id', $userIds)->delete();
        }
    }
}
