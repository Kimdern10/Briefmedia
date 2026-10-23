<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polls can be ended: "is_closed" is the admin's Close voting switch, "closes_at" an optional time
 * the poll closes by itself. Readers of a closed poll see the final results.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            if (! Schema::hasColumn('polls', 'is_closed')) {
                $table->boolean('is_closed')->default(false)->after('question');
            }
            if (! Schema::hasColumn('polls', 'closes_at')) {
                $table->timestamp('closes_at')->nullable()->after('is_closed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['is_closed', 'closes_at']);
        });
    }
};
