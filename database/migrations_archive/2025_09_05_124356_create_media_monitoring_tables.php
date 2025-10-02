<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Media sources configuration (RSS feeds)
        Schema::create('media_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('rss_url');
            $table->string('category')->default('general'); // tech/news/business
            $table->string('language', 5)->default('nl'); // nl/en/fr
            $table->boolean('is_active')->default(true);
            $table->integer('check_frequency')->default(30); // minutes
            $table->timestamp('last_checked_at')->nullable();
            $table->integer('reliability_score')->default(100);
            $table->timestamps();
            
            $table->index(['is_active', 'last_checked_at']);
        });

        // User monitoring preferences
        Schema::create('user_media_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('keywords'); // ["ChatGPT", "OpenAI", "AI"]
            $table->json('exclude_keywords')->nullable(); // ["casino", "crypto"]
            $table->boolean('is_active')->default(true);
            $table->boolean('email_alerts')->default(false);
            $table->enum('alert_frequency', ['realtime', 'hourly', 'daily'])->default('daily');
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });

        // RSS feed items cache
        Schema::create('rss_feed_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('media_sources')->onDelete('cascade');
            $table->string('guid')->unique(); // RSS unique identifier
            $table->text('title');
            $table->text('link');
            $table->text('description')->nullable();
            $table->datetime('pub_date');
            $table->string('author')->nullable();
            $table->text('raw_content')->nullable(); // Full article if available
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['source_id', 'processed']);
            $table->index('pub_date');
        });

        // Found articles for users
        Schema::create('user_media_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('monitor_id')->constrained('user_media_monitors')->onDelete('cascade');
            $table->string('source_name');
            $table->text('article_title');
            $table->text('article_url');
            $table->text('article_excerpt')->nullable();
            $table->datetime('published_at');
            $table->integer('relevance_score')->default(0); // 0-100
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->text('ai_summary')->nullable();
            $table->json('found_keywords')->nullable(); // Which keywords were found
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_read']);
            $table->index(['monitor_id', 'published_at']);
            $table->index('relevance_score');
        });

        // AI analysis logs for cost tracking
        Schema::create('media_ai_analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_item_id')->nullable()->constrained('rss_feed_cache');
            $table->string('ai_provider'); // openai/anthropic
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 8, 6)->default(0);
            $table->integer('processing_time_ms')->default(0);
            $table->json('matched_keywords')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100
            $table->timestamps();
            
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_ai_analysis_logs');
        Schema::dropIfExists('user_media_mentions');
        Schema::dropIfExists('rss_feed_cache');
        Schema::dropIfExists('user_media_monitors');
        Schema::dropIfExists('media_sources');
    }
};