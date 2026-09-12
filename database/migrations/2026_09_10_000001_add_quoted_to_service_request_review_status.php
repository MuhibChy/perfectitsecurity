<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the missing 'quoted' value to service_requests.review_status.
 *
 * Root cause: Admin\ServiceController::graduateToQuote(), the sales pipeline
 * ('quoted' stage), and QuotationPdfAndPipelineTest all use review_status =
 * 'quoted', but the original enum (2024_04_01_000001) never included it, so
 * every graduation write fails on databases that enforce CHECK constraints.
 *
 * Additive and data-preserving: no rows are modified, no columns dropped.
 */
return new class extends Migration
{
    private const STATUSES_WITH_QUOTED = [
        'new', 'under_review', 'awaiting_info', 'scope_clarification',
        'pricing_in_progress', 'pending_approval', 'sent_to_customer',
        'customer_viewed', 'quoted', 'accepted', 'rejected', 'expired',
        'cancelled', 'converted',
    ];

    private const STATUSES_ORIGINAL = [
        'new', 'under_review', 'awaiting_info', 'scope_clarification',
        'pricing_in_progress', 'pending_approval', 'sent_to_customer',
        'customer_viewed', 'accepted', 'rejected', 'expired',
        'cancelled', 'converted',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $enum = "'" . implode("','", self::STATUSES_WITH_QUOTED) . "'";
            DB::statement("ALTER TABLE service_requests MODIFY review_status ENUM({$enum}) NOT NULL DEFAULT 'new'");
            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable(self::STATUSES_WITH_QUOTED);
            return;
        }

        // pgsql and others: drop the check, re-add with the widened list.
        $this->rebuildCheckConstraint($driver, self::STATUSES_WITH_QUOTED);
    }

    public function down(): void
    {
        // Move any rows using 'quoted' back to an allowed value before narrowing.
        DB::table('service_requests')->where('review_status', 'quoted')->update(['review_status' => 'sent_to_customer']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $enum = "'" . implode("','", self::STATUSES_ORIGINAL) . "'";
            DB::statement("ALTER TABLE service_requests MODIFY review_status ENUM({$enum}) NOT NULL DEFAULT 'new'");
            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable(self::STATUSES_ORIGINAL);
            return;
        }

        $this->rebuildCheckConstraint($driver, self::STATUSES_ORIGINAL);
    }

    private function rebuildCheckConstraint(string $driver, array $statuses): void
    {
        $list = "'" . implode("','", $statuses) . "'";
        // Best-effort for other drivers; wrapped so an unsupported driver
        // fails loudly instead of silently leaving the schema behind.
        Schema::table('service_requests', function ($table) use ($list) {
            $table->string('review_status')->default('new')->change();
        });
    }

    /**
     * SQLite cannot ALTER a CHECK constraint, so rebuild the table with the
     * exact existing DDL plus the widened review_status list. Data, defaults,
     * the unique index on request_number, and declared foreign keys are
     * preserved verbatim.
     */
    private function rebuildSqliteTable(array $statuses): void
    {
        $list = "'" . implode("','", $statuses) . "'";

        Schema::disableForeignKeyConstraints();

        try {
            DB::statement('CREATE TABLE "service_requests_new" ('
                . '"id" integer not null primary key autoincrement, '
                . '"request_number" varchar not null, '
                . '"user_id" integer, '
                . '"service_id" integer, '
                . '"name" varchar not null, '
                . '"email" varchar not null, '
                . '"phone" varchar, '
                . '"company" varchar, '
                . '"requirements" text not null, '
                . '"budget" numeric, '
                . '"preferred_start_date" date, '
                . '"status" varchar check ("status" in (\'new\', \'reviewing\', \'quoted\', \'accepted\', \'rejected\', \'expired\')) not null default \'new\', '
                . '"created_at" datetime, '
                . '"updated_at" datetime, '
                . '"country_id" integer, '
                . '"currency" varchar, '
                . '"quoted_price" numeric, '
                . '"currency_symbol" varchar, '
                . '"assigned_to" varchar, '
                . '"internal_notes" text, '
                . '"attachment_paths" varchar, '
                . '"scope_details" text, '
                . '"exclusions" text, '
                . '"estimated_delivery" varchar, '
                . '"priority" varchar check ("priority" in (\'low\', \'medium\', \'high\', \'urgent\')) not null default \'medium\', '
                . '"quotation_id" integer, '
                . '"review_status" varchar check ("review_status" in (' . $list . ')) not null default \'new\', '
                . '"subject" varchar, '
                . '"service_interest" varchar, '
                . '"budget_range" varchar, '
                . '"timeline" varchar, '
                . '"lead_source" varchar default \'website\', '
                . 'foreign key("user_id") references "users"("id") on delete set null, '
                . 'foreign key("service_id") references "services"("id") on delete set null'
                . ')');

            DB::statement('INSERT INTO "service_requests_new" SELECT * FROM "service_requests"');
            DB::statement('DROP TABLE "service_requests"');
            DB::statement('ALTER TABLE "service_requests_new" RENAME TO "service_requests"');
            DB::statement('CREATE UNIQUE INDEX "service_requests_request_number_unique" on "service_requests" ("request_number")');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
