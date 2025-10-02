<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Complete AdCompro Database Schema
     *
     * This migration creates all required tables by importing the schema.sql file.
     * Generated from: progress.adcompro.app production database
     * Date: 2025-10-02
     * Tables: 64 tables (all AdCompro functionality)
     */
    public function up(): void
    {
        $schemaPath = database_path('schema.sql');

        if (!file_exists($schemaPath)) {
            throw new \Exception("Schema file not found at: {$schemaPath}");
        }

        $sql = file_get_contents($schemaPath);

        // Remove any existing DROP TABLE statements
        $sql = preg_replace('/DROP TABLE IF EXISTS.*?;/i', '', $sql);

        // Split into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
        );

        // Execute each statement
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                DB::unprepared($statement . ';');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // List all tables to drop (in reverse dependency order)
        $tables = [
            // Pivot & relationship tables first
            'contact_companies',
            'contact_activities',
            'customer_activities',
            'company_activities',
            'service_activities',
            'calendar_activities',
            'project_users',
            'project_companies',
            'project_services',
            'plugin_activities',
            'company_plugins',

            // Data tables
            'invoice_lines',
            'invoices',
            'time_entries',
            'project_monthly_fees',
            'project_additional_costs',
            'project_monthly_additional_costs',
            'monthly_intercompany_charges',
            'invoice_draft_actions',

            // Project hierarchy (bottom-up)
            'project_subtasks',
            'project_tasks',
            'project_milestones',
            'projects',

            // Templates
            'template_subtasks',
            'template_tasks',
            'template_milestones',
            'project_templates',

            // Services
            'service_subtasks',
            'service_tasks',
            'service_milestones',
            'services',
            'service_categories',

            // Media & Social
            'project_media_mentions',
            'project_media_campaigns',
            'social_media_mentions',
            'social_media_sources',

            // Calendar
            'calendar_sync_logs',
            'calendar_events',
            'user_ms_graph_tokens',

            // AI
            'ai_usage_logs',
            'ai_learning_feedback',
            'ai_settings',
            'project_ai_settings',

            // Core entities
            'contacts',
            'customers',
            'users',
            'companies',

            // Settings & Config
            'invoice_templates',
            'simplified_theme_settings',
            'settings',
            'plugins',

            // Laravel system tables
            'personal_access_tokens',
            'sessions',
            'failed_jobs',
            'job_batches',
            'jobs',
            'cache_locks',
            'cache',
            'password_reset_tokens',
        ];

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
