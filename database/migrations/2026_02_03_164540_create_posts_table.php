<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Admin who created the post
            $table->foreignId('admin_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Category
            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('content');

            // Featured images (1 required, 3 optional)
            $table->string('featured_image_1'); // REQUIRED
            $table->string('featured_image_2')->nullable();
            $table->string('featured_image_3')->nullable();
            $table->string('featured_image_4')->nullable();

            // Status
            $table->enum('status', ['draft', 'published'])->default('draft');

            // Basic SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // =========================================
            // ENHANCED SEO FIELDS - REMOVED after() CLAUSES
            // =========================================
            
            // Auto SEO toggle
            $table->boolean('enable_auto_seo')->default(true);
            
            // Meta image for search engines
            $table->string('meta_image')->nullable();
            
            // Canonical URL to avoid duplicate content
            $table->string('canonical_url')->nullable();
            
            // Open Graph (Facebook, LinkedIn)
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            
            // Twitter Card
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            
            // Schema markup (JSON-LD)
            $table->text('schema_markup')->nullable();
            
            // Robots meta directives
            $table->boolean('no_index')->default(false);
            $table->boolean('no_follow')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};