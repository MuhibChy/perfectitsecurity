<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widens users.role to every RoleRegistry role (adds training_manager).
 *
 * Root cause: 2024_01_01_000001 hardcoded 11 roles; training_manager became
 * a first-class role later (routes, training management, certificates) but
 * any insert with role=training_manager fails on CHECK-enforcing databases.
 * The allowed list is derived from RoleRegistry so code and schema agree.
 *
 * Data-preserving: no rows modified, no columns dropped.
 */
return new class extends Migration
{
    public static function allowedRoles(): array
    {
        return array_map(fn ($d) => $d['name'], \App\Support\RoleRegistry::definitions());
    }

    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $enum = "'" . implode("','", self::allowedRoles()) . "'";
            DB::statement("ALTER TABLE users MODIFY role ENUM({$enum}) NOT NULL DEFAULT 'customer'");
            return;
        }
        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsers(self::allowedRoles());
            return;
        }
        // Other drivers: fall back to plain string (drops the enum check).
        Schema::table('users', function ($table) {
            $table->string('role')->default('customer')->change();
        });
    }

    public function down(): void
    {
        // Move rows back into the old set before narrowing.
        DB::table('users')->whereNotIn('role', [
            'super_admin', 'admin', 'finance_manager', 'support_manager',
            'support_agent', 'project_manager', 'employee', 'freelancer',
            'commission_agent', 'sales_agent', 'customer',
        ])->update(['role' => 'employee']);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','finance_manager','support_manager','support_agent','project_manager','employee','freelancer','commission_agent','sales_agent','customer') NOT NULL DEFAULT 'customer'");
            return;
        }
        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsers([
                'super_admin', 'admin', 'finance_manager', 'support_manager',
                'support_agent', 'project_manager', 'employee', 'freelancer',
                'commission_agent', 'sales_agent', 'customer',
            ]);
            return;
        }
        Schema::table('users', function ($table) {
            $table->string('role')->default('customer')->change();
        });
    }

    /**
     * SQLite cannot ALTER a CHECK: rebuild users with identical DDL except
     * the widened role list. Column order, defaults, inline uniques and data
     * are preserved; standalone indexes and the autoincrement sequence are
     * restored afterwards. Foreign keys stay valid (referenced by table name).
     */
    private function rebuildSqliteUsers(array $roles): void
    {
        $ddl = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='users'")->sql;
        $list = "'" . implode("','", $roles) . "'";
        $newDdl = preg_replace(
            '/"role" varchar check \("role" in \([^)]*\)\)/',
            '"role" varchar check ("role" in (' . $list . '))',
            $ddl,
            1
        );
        abort_if($newDdl === null || $newDdl === $ddl, 500, 'users.role CHECK pattern not found; refusing to rebuild.');

        $indexes = DB::select("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='users' AND sql IS NOT NULL");
        $triggers = DB::select("SELECT sql FROM sqlite_master WHERE type='trigger' AND tbl_name='users'");

        Schema::disableForeignKeyConstraints();
        try {
            DB::statement(str_replace('CREATE TABLE "users"', 'CREATE TABLE "users_new"', $newDdl));
            DB::statement('INSERT INTO "users_new" SELECT * FROM "users"');
            DB::statement('DROP TABLE "users"');
            DB::statement('ALTER TABLE "users_new" RENAME TO "users"');
            foreach ($indexes as $index) {
                DB::statement($index->sql);
            }
            foreach ($triggers as $trigger) {
                DB::statement($trigger->sql);
            }
            DB::statement("UPDATE sqlite_sequence SET seq = (SELECT COALESCE(MAX(id), 0) FROM users) WHERE name='users'");
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
