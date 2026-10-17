<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Recreates the live-server problem on a scratch table: an `id` column with no
 * AUTO_INCREMENT and no primary key, so inserts without an id fail.
 */
class RestoreAutoIncrementMigrationTest extends TestCase
{
    private const SCRATCH_TABLE = 'zz_auto_increment_repair_test';

    protected function tearDown(): void
    {
        Schema::dropIfExists(self::SCRATCH_TABLE);

        parent::tearDown();
    }

    public function test_it_restores_auto_numbering_so_inserts_without_an_id_work(): void
    {
        Schema::dropIfExists(self::SCRATCH_TABLE);
        DB::statement('CREATE TABLE `'.self::SCRATCH_TABLE.'` (`id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(50) NULL)');

        $migration = require database_path('migrations/2026_10_17_094058_restore_auto_increment_on_id_columns.php');
        $migration->up();

        $column = DB::selectOne(
            'SELECT extra AS extra, column_key AS column_key FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [self::SCRATCH_TABLE, 'id']
        );

        $this->assertStringContainsString('auto_increment', $column->extra);
        $this->assertSame('PRI', $column->column_key);

        DB::table(self::SCRATCH_TABLE)->insert(['name' => 'first']);
        DB::table(self::SCRATCH_TABLE)->insert(['name' => 'second']);

        $this->assertSame([1, 2], DB::table(self::SCRATCH_TABLE)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_it_leaves_healthy_tables_alone(): void
    {
        $before = DB::selectOne(
            "SELECT extra AS extra FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'userprofiles' AND column_name = 'id'"
        );

        $migration = require database_path('migrations/2026_10_17_094058_restore_auto_increment_on_id_columns.php');
        $migration->up();

        $after = DB::selectOne(
            "SELECT extra AS extra FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'userprofiles' AND column_name = 'id'"
        );

        $this->assertSame($before->extra, $after->extra);
    }
}
