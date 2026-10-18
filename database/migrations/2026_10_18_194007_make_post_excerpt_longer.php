<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * posts.excerpt was a 255-character column; summaries can now be up to 2,000 characters.
 * Raw SQL so no extra package (doctrine/dbal) is needed to change the column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `posts` MODIFY `excerpt` TEXT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE `posts` SET `excerpt` = LEFT(`excerpt`, 255) WHERE CHAR_LENGTH(`excerpt`) > 255');
            DB::statement('ALTER TABLE `posts` MODIFY `excerpt` VARCHAR(255) NULL');
        }
    }
};
