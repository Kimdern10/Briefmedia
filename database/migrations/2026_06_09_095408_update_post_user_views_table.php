<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_user_views', function (Blueprint $table) {
            if (!Schema::hasColumn('post_user_views', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('user_id');
            }

            $table->dropForeign(['post_id']);
            $table->dropForeign(['user_id']);

            $table->dropUnique(['post_id', 'user_id']);

            $table->unique(['post_id', 'user_id', 'ip_address']);

            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('post_user_views', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropForeign(['user_id']);

            $table->dropUnique(['post_id', 'user_id', 'ip_address']);
            $table->dropColumn('ip_address');

            $table->unique(['post_id', 'user_id']);

            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
