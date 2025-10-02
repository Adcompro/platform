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
        // Add campaign_id to user_media_monitors
        Schema::table('user_media_monitors', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->after('user_id');
            $table->index('campaign_id');
        });

        // Project Media Campaigns table
        Schema::create('project_media_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('press_release_date');
            $table->enum('campaign_type', [
                'product_launch',
                'feature_announcement',
                'company_news',
                'event',
                'partnership',
                'other'
            ])->default('other');
            $table->string('target_audience')->nullable();
            $table->integer('expected_reach')->nullable();
            $table->integer('actual_reach')->nullable();
            $table->json('keywords')->nullable();
            $table->enum('status', ['planning', 'active', 'completed', 'on_hold'])->default('planning');
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index('press_release_date');
            $table->foreign('parent_id')->references('id')->on('project_media_campaigns')->nullOnDelete();
        });

        // Link mentions to projects and campaigns
        Schema::create('project_media_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('project_media_campaigns')->onDelete('set null');
            $table->foreignId('user_media_mention_id')->constrained('user_media_mentions')->onDelete('cascade');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->enum('assignment_method', ['automatic', 'manual', 'ai_suggested'])->default('manual');
            $table->integer('confidence_score')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'user_media_mention_id']);
            $table->index(['campaign_id', 'created_at']);
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });

        // Add foreign key for campaign_id in monitors
        Schema::table('user_media_monitors', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('project_media_campaigns')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key first
        Schema::table('user_media_monitors', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        // Drop tables
        Schema::dropIfExists('project_media_mentions');
        Schema::dropIfExists('project_media_campaigns');
    }
};