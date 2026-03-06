<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();

            // Link to posts table
            $table->foreignId('post_id')
                ->constrained('posts') // ✅ references posts.id
                ->cascadeOnDelete();

            // User who made the comment
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Comment content
            $table->text('content');

            // Reply support
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('post_comments')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};
