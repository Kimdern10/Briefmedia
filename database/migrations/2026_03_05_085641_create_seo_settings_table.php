<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();

            // Basic SEO
            $table->string('site_name')->nullable();
            $table->string('default_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('author')->nullable();
            $table->string('canonical_url')->nullable();

            // Open Graph
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('website');

            // Twitter
            $table->string('twitter_card')->default('summary');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_site')->nullable();

            // Other
            $table->string('favicon')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_verification')->nullable();
            $table->string('robots_meta')->default('index, follow');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};