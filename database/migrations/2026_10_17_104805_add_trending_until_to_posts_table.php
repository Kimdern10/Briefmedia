<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin mark a post as trending for a limited time (the 🔥 Trend button in the
 * admin posts list). While trending_until is in the future, the post is shown first in
 * the Trending sections.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'trending_until')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('trending_until')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'trending_until')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['trending_until']);
            $table->dropColumn('trending_until');
        });
    }
};
