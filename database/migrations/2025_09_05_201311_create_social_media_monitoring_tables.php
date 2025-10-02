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
        // Social media platform configurations
        Schema::create('social_media_sources', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['twitter', 'linkedin', 'facebook', 'instagram', 'youtube']);
            $table->string('account_name')->nullable(); // Account to monitor
            $table->string('account_id')->nullable(); // Platform-specific ID
            $table->json('api_credentials')->nullable(); // Encrypted API keys
            $table->json('monitoring_config')->nullable(); // What to monitor (mentions, hashtags, etc)
            $table->boolean('is_active')->default(true);
            $table->integer('check_frequency')->default(5); // minutes
            $table->timestamp('last_checked_at')->nullable();
            $table->string('last_post_id')->nullable(); // For pagination/since_id
            $table->integer('rate_limit_remaining')->nullable();
            $table->timestamp('rate_limit_reset_at')->nullable();
            $table->timestamps();
            
            $table->index(['platform', 'is_active']);
            $table->index('last_checked_at');
        });

        // Social media posts/mentions
        Schema::create('social_media_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('social_media_sources')->onDelete('cascade');
            $table->string('platform_post_id')->unique(); // Platform's unique ID
            $table->string('author_name');
            $table->string('author_handle')->nullable();
            $table->string('author_profile_url')->nullable();
            $table->integer('author_followers')->nullable();
            $table->boolean('author_verified')->default(false);
            $table->text('content');
            $table->json('hashtags')->nullable();
            $table->json('mentions')->nullable(); // @mentions
            $table->json('urls')->nullable();
            $table->json('media_urls')->nullable(); // Images/videos
            $table->string('post_url')->nullable();
            $table->datetime('published_at');
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('views_count')->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable(); // Calculated engagement %
            $table->enum('post_type', ['post', 'reply', 'share', 'story'])->default('post');
            $table->string('in_reply_to')->nullable(); // Parent post ID
            $table->boolean('is_repost')->default(false);
            $table->json('raw_data')->nullable(); // Store complete API response
            $table->timestamps();
            
            $table->index(['source_id', 'published_at']);
            $table->index('author_handle');
            $table->index('engagement_rate');
        });

        // Link social mentions to user monitors
        Schema::create('user_social_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('monitor_id')->constrained('user_media_monitors')->onDelete('cascade');
            $table->foreignId('social_mention_id')->constrained('social_media_mentions')->onDelete('cascade');
            $table->integer('relevance_score')->default(0);
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('requires_response')->default(false);
            $table->text('response_draft')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->json('matched_keywords')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'monitor_id', 'social_mention_id'], 'user_monitor_social_unique');
            $table->index(['user_id', 'is_read']);
            $table->index('relevance_score');
        });

        // Track engagement metrics over time
        Schema::create('social_engagement_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_mention_id')->constrained('social_media_mentions')->onDelete('cascade');
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('views_count')->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->timestamp('measured_at');
            $table->timestamps();
            
            $table->index(['social_mention_id', 'measured_at']);
        });

        // Social media campaigns link
        Schema::create('project_social_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('project_media_campaigns')->onDelete('set null');
            $table->foreignId('social_mention_id')->constrained('social_media_mentions')->onDelete('cascade');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->enum('assignment_method', ['automatic', 'manual', 'ai_suggested'])->default('manual');
            $table->integer('confidence_score')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'social_mention_id']);
            $table->index(['campaign_id', 'created_at']);
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });

        // Add social platform selection to monitors
        Schema::table('user_media_monitors', function (Blueprint $table) {
            $table->json('social_platforms')->nullable()->after('exclude_keywords');
            $table->boolean('monitor_social')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_media_monitors', function (Blueprint $table) {
            $table->dropColumn(['social_platforms', 'monitor_social']);
        });
        
        Schema::dropIfExists('project_social_mentions');
        Schema::dropIfExists('social_engagement_metrics');
        Schema::dropIfExists('user_social_mentions');
        Schema::dropIfExists('social_media_mentions');
        Schema::dropIfExists('social_media_sources');
    }
};