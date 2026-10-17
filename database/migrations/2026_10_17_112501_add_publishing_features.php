<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - posts.publish_at: scheduled posts (status "scheduled") go live at this time
 * - posts.image_caption / image_credit: shown under the main photo
 * - post_comments.approved_at: comments only show publicly once approved
 *   (existing comments are marked approved so nothing disappears)
 * - post_daily_views: views per post per day, for "Most read today"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'publish_at')) {
                $table->timestamp('publish_at')->nullable()->index();
            }
            if (! Schema::hasColumn('posts', 'image_caption')) {
                $table->string('image_caption', 255)->nullable();
            }
            if (! Schema::hasColumn('posts', 'image_credit')) {
                $table->string('image_credit', 120)->nullable();
            }
        });

        if (! Schema::hasColumn('post_comments', 'approved_at')) {
            Schema::table('post_comments', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->index();
            });

            DB::table('post_comments')->whereNull('approved_at')->update(['approved_at' => DB::raw('created_at')]);
        }

        if (! Schema::hasTable('post_daily_views')) {
            Schema::create('post_daily_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id');
                $table->date('date');
                $table->unsignedInteger('views')->default(0);
                $table->unique(['post_id', 'date']);
                $table->index(['date', 'views']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_daily_views');

        if (Schema::hasColumn('post_comments', 'approved_at')) {
            Schema::table('post_comments', function (Blueprint $table) {
                $table->dropIndex(['approved_at']);
                $table->dropColumn('approved_at');
            });
        }

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'publish_at')) {
                $table->dropIndex(['publish_at']);
                $table->dropColumn('publish_at');
            }
            foreach (['image_caption', 'image_credit'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
