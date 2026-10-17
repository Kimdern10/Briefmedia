<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Repairs tables whose `id` column lost AUTO_INCREMENT (and sometimes its PRIMARY KEY),
 * which happens when a database is imported from an export that left out indexes.
 *
 * On the live server `userprofiles.id` had no auto-numbering, so saving a profile for a user
 * without a profile row failed with "Field 'id' doesn't have a default value". This checks
 * every table and only touches the broken ones, so it does nothing on a healthy database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $brokenColumns = DB::select(
            "SELECT table_name AS table_name, column_type AS column_type
               FROM information_schema.columns
              WHERE table_schema = DATABASE()
                AND column_name = 'id'
                AND data_type IN ('tinyint', 'smallint', 'mediumint', 'int', 'bigint')
                AND extra NOT LIKE '%auto_increment%'"
        );

        if ($brokenColumns === []) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($brokenColumns as $column) {
                $table = str_replace('`', '', $column->table_name);

                try {
                    if (! $this->hasPrimaryKey($table)) {
                        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
                    }

                    DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$column->column_type} NOT NULL AUTO_INCREMENT");
                } catch (Throwable $e) {
                    // Keep going so one problem table doesn't block the others.
                    Log::warning("Could not restore AUTO_INCREMENT on {$table}.id: {$e->getMessage()}");
                    if (defined('STDERR')) {
                        fwrite(STDERR, "  Skipped {$table}.id: {$e->getMessage()}".PHP_EOL);
                    }
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Removing auto-numbering again would only re-break inserts, so there is nothing to undo.
    }

    private function hasPrimaryKey(string $table): bool
    {
        return DB::selectOne(
            "SELECT COUNT(*) AS total
               FROM information_schema.table_constraints
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND constraint_type = 'PRIMARY KEY'",
            [$table]
        )->total > 0;
    }
};
