<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoFieldsToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('posts', 'enable_auto_seo')) {
                $table->boolean('enable_auto_seo')->default(true)->after('meta_keywords');
            }
            if (!Schema::hasColumn('posts', 'meta_image')) {
                $table->string('meta_image')->nullable()->after('enable_auto_seo');
            }
            if (!Schema::hasColumn('posts', 'canonical_url')) {
                $table->string('canonical_url')->nullable()->after('meta_image');
            }
            if (!Schema::hasColumn('posts', 'og_title')) {
                $table->string('og_title')->nullable()->after('canonical_url');
            }
            if (!Schema::hasColumn('posts', 'og_description')) {
                $table->text('og_description')->nullable()->after('og_title');
            }
            if (!Schema::hasColumn('posts', 'og_image')) {
                $table->string('og_image')->nullable()->after('og_description');
            }
            if (!Schema::hasColumn('posts', 'twitter_title')) {
                $table->string('twitter_title')->nullable()->after('og_image');
            }
            if (!Schema::hasColumn('posts', 'twitter_description')) {
                $table->text('twitter_description')->nullable()->after('twitter_title');
            }
            if (!Schema::hasColumn('posts', 'twitter_image')) {
                $table->string('twitter_image')->nullable()->after('twitter_description');
            }
            if (!Schema::hasColumn('posts', 'schema_markup')) {
                $table->text('schema_markup')->nullable()->after('twitter_image');
            }
            if (!Schema::hasColumn('posts', 'no_index')) {
                $table->boolean('no_index')->default(false)->after('schema_markup');
            }
            if (!Schema::hasColumn('posts', 'no_follow')) {
                $table->boolean('no_follow')->default(false)->after('no_index');
            }
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $columns = [
                'enable_auto_seo',
                'meta_image',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image',
                'twitter_title',
                'twitter_description',
                'twitter_image',
                'schema_markup',
                'no_index',
                'no_follow'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}