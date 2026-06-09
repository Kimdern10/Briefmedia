<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_user_views', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('user_id');

            $table->dropUnique(['post_id', 'user_id']);

            $table->unique(['post_id', 'user_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::table('post_user_views', function (Blueprint $table) {
            $table->dropColumn('ip_address');

            $table->unique(['post_id', 'user_id']);
        });
    }
};