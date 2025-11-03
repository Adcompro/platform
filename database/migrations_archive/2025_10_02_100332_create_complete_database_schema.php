<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Complete AdCompro Database Schema
     * 
     * This migration creates all required tables for a fresh installation.
     * Generated from: progress.adcompro.app database
     * Date: 2025-10-02
     */
    public function up(): void
    {
        // ============================================
        // LARAVEL SYSTEM TABLES
        // ============================================
        
        // Users & Authentication
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'project_manager', 'user', 'reader'])->default('user');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Cache & Jobs
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ============================================
        // COMPANY & USER MANAGEMENT
        // ============================================
        
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('coc_number')->nullable();
            $table->decimal('default_hourly_rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('bank_details')->nullable();
            $table->json('invoice_settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // ============================================
        // CUSTOMER MANAGEMENT
        // ============================================
        
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->enum('status', ['active', 'inactive', 'prospect'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['customer_id', 'company_id']);
        });

        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // ============================================
        // CONTACT MANAGEMENT
        // ============================================
        
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('contact_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // ============================================
        // PROJECT MANAGEMENT
        // ============================================
        
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planning');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->decimal('budget_amount', 10, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['manager', 'member', 'viewer'])->default('member');
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('billing_method', ['fixed_amount', 'actual_hours'])->default('actual_hours');
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'company_id']);
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'on_hold'])->default('not_started');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_milestone_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'blocked'])->default('not_started');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Project Financial Tables
        Schema::create('project_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('extended');
            $table->date('date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        Schema::create('project_monthly_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('extended');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('project_monthly_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('fee_amount', 10, 2);
            $table->decimal('rollover_previous', 10, 2)->default(0);
            $table->decimal('rollover_next', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'year', 'month']);
        });

        Schema::create('project_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Media Campaigns
        Schema::create('project_media_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->json('keywords')->nullable();
            $table->timestamps();
        });

        Schema::create('project_media_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_media_campaign_id')->constrained()->onDelete('cascade');
            $table->string('source');
            $table->text('content');
            $table->string('url')->nullable();
            $table->timestamp('published_at');
            $table->integer('reach')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->timestamps();
        });

        // ============================================
        // PROJECT TEMPLATES
        // ============================================
        
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('template_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_template_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('template_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_milestone_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ============================================
        // SERVICE CATALOG
        // ============================================
        
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->default('hourly_rate');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_milestone_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed_price', 'hourly_rate'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ============================================
        // TIME TRACKING
        // ============================================
        
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_milestone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_subtask_id')->nullable()->constrained('project_subtasks')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('hours', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // ============================================
        // INVOICING
        // ============================================
        
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'finalized', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('html_content');
            $table->json('styles')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('invoice_draft_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['bundled', 'deferred', 'modified', 'split'])->default('bundled');
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_intercompany_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('to_company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ============================================
        // CALENDAR & MICROSOFT GRAPH
        // ============================================
        
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ms_graph_id')->nullable()->unique();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('organizer_email')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->integer('events_synced')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('user_ms_graph_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('ms_graph_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // ============================================
        // AI & SETTINGS
        // ============================================
        
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('feature');
            $table->string('model')->default('gpt-4');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_learning_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_entry_id')->constrained()->onDelete('cascade');
            $table->boolean('was_correct')->default(false);
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::create('simplified_theme_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('primary_color')->default('#3b82f6');
            $table->string('secondary_color')->default('#64748b');
            $table->enum('font_size_base', ['10px', '11px', '12px', '13px', '14px', '15px', '16px'])->default('14px');
            $table->enum('table_header_style', ['light', 'dark', 'colored', 'bold'])->default('light');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================
        // RSS & CACHE
        // ============================================
        
        Schema::create('rss_feed_cache', function (Blueprint $table) {
            $table->id();
            $table->string('feed_url');
            $table->longText('content');
            $table->timestamp('cached_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to respect foreign key constraints
        Schema::dropIfExists('rss_feed_cache');
        Schema::dropIfExists('simplified_theme_settings');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('ai_settings');
        Schema::dropIfExists('ai_learning_feedback');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ms_graph_tokens');
        Schema::dropIfExists('user_ms_graph_tokens');
        Schema::dropIfExists('calendar_sync_logs');
        Schema::dropIfExists('calendar_activities');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('monthly_intercompany_charges');
        Schema::dropIfExists('invoice_draft_actions');
        Schema::dropIfExists('invoice_templates');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('service_activities');
        Schema::dropIfExists('service_tasks');
        Schema::dropIfExists('service_milestones');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('template_tasks');
        Schema::dropIfExists('template_milestones');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('project_media_mentions');
        Schema::dropIfExists('project_media_campaigns');
        Schema::dropIfExists('project_services');
        Schema::dropIfExists('project_monthly_fees');
        Schema::dropIfExists('project_monthly_additional_costs');
        Schema::dropIfExists('project_additional_costs');
        Schema::dropIfExists('project_subtasks');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_companies');
        Schema::dropIfExists('project_users');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contact_activities');
        Schema::dropIfExists('contact_companies');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customer_companies');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('company_activities');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};