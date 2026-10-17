<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - tags / post_tag: topics like "Soludo" or "ASUU", each with its own page
 * - post_bookmarks: readers' "Save for later" list
 * - posts.is_live / post_live_updates: live coverage posts with timestamped updates
 * - polls / poll_options / poll_votes: a reader poll inside an article
 * - posts.content_updated_at / correction_note: "Updated" label and correction note
 * - ad_slots: ad or sponsor code per placement, managed in the admin
 * - posts.announced_at / push_sent_at: when a post went out to Facebook/Telegram and as
 *   a breaking-news push alert. Existing posts are marked as already sent, so editing an
 *   old story never re-posts it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'is_live')) {
                $table->boolean('is_live')->default(false)->index();
            }
            if (! Schema::hasColumn('posts', 'content_updated_at')) {
                $table->timestamp('content_updated_at')->nullable();
            }
            if (! Schema::hasColumn('posts', 'correction_note')) {
                $table->text('correction_note')->nullable();
            }
            if (! Schema::hasColumn('posts', 'announced_at')) {
                $table->timestamp('announced_at')->nullable();
            }
            if (! Schema::hasColumn('posts', 'push_sent_at')) {
                $table->timestamp('push_sent_at')->nullable();
            }
        });

        DB::table('posts')->whereNull('announced_at')->update(['announced_at' => now()]);
        DB::table('posts')->whereNull('push_sent_at')->update(['push_sent_at' => now()]);

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name', 60)->unique();
                $table->string('slug', 80)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('post_tag')) {
            Schema::create('post_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('tag_id');
                $table->primary(['post_id', 'tag_id']);
                $table->index('tag_id');
            });
        }

        if (! Schema::hasTable('post_bookmarks')) {
            Schema::create('post_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('post_id');
                $table->timestamps();
                $table->unique(['user_id', 'post_id']);
                $table->index('post_id');
            });
        }

        if (! Schema::hasTable('post_live_updates')) {
            Schema::create('post_live_updates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->string('title', 160)->nullable();
                $table->text('body');
                $table->timestamps();
                $table->index(['post_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('polls')) {
            Schema::create('polls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id')->unique();
                $table->string('question', 200);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('poll_options')) {
            Schema::create('poll_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('poll_id');
                $table->string('label', 120);
                $table->unsignedInteger('votes')->default(0);
                $table->unsignedTinyInteger('position')->default(0);
                $table->timestamps();
                $table->index('poll_id');
            });
        }

        if (! Schema::hasTable('poll_votes')) {
            Schema::create('poll_votes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('poll_id');
                $table->unsignedBigInteger('poll_option_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('voter_key', 80);
                $table->timestamps();
                $table->unique(['poll_id', 'voter_key']);
            });
        }

        if (! Schema::hasTable('ad_slots')) {
            Schema::create('ad_slots', function (Blueprint $table) {
                $table->id();
                $table->string('placement', 40)->unique();
                $table->text('code')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_slots');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('post_live_updates');
        Schema::dropIfExists('post_bookmarks');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');

        Schema::table('posts', function (Blueprint $table) {
            foreach (['is_live', 'content_updated_at', 'correction_note', 'announced_at', 'push_sent_at'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
