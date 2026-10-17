<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post options set in the admin post form:
 * - headline_until: shown first in the homepage slider until this time (1 day after ticking)
 * - breaking_until: breaking news (red ticker and badge) until this time (1 day after ticking)
 * - video_url: optional YouTube / Facebook / Vimeo video shown on the article
 * - author_name: optional writer's name shown instead of the admin account name
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'headline_until')) {
                $table->timestamp('headline_until')->nullable()->index();
            }
            if (! Schema::hasColumn('posts', 'breaking_until')) {
                $table->timestamp('breaking_until')->nullable()->index();
            }
            if (! Schema::hasColumn('posts', 'video_url')) {
                $table->string('video_url', 500)->nullable();
            }
            if (! Schema::hasColumn('posts', 'author_name')) {
                $table->string('author_name', 120)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            foreach (['headline_until', 'breaking_until', 'video_url', 'author_name'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    if (in_array($column, ['headline_until', 'breaking_until'], true)) {
                        $table->dropIndex([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
