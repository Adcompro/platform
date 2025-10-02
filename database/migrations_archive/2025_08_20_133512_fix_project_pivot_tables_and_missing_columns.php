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
        // =====================================
        // ADD MISSING COLUMNS TO project_users TABLE
        // =====================================
        Schema::table('project_users', function (Blueprint $table) {
            // Check en voeg ontbrekende kolommen toe
            if (!Schema::hasColumn('project_users', 'role_override')) {
                $table->string('role_override')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('project_users', 'can_edit_fee')) {
                $table->boolean('can_edit_fee')->default(false)->after('role_override');
            }
            if (!Schema::hasColumn('project_users', 'can_view_financials')) {
                $table->boolean('can_view_financials')->default(false)->after('can_edit_fee');
            }
            if (!Schema::hasColumn('project_users', 'can_log_time')) {
                $table->boolean('can_log_time')->default(true)->after('can_view_financials');
            }
            if (!Schema::hasColumn('project_users', 'can_approve_time')) {
                $table->boolean('can_approve_time')->default(false)->after('can_log_time');
            }
            if (!Schema::hasColumn('project_users', 'added_by')) {
                $table->foreignId('added_by')->nullable()->constrained('users')->after('can_approve_time');
            }
            if (!Schema::hasColumn('project_users', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('added_by');
            }
        });

        // =====================================
        // ADD MISSING COLUMNS TO project_companies TABLE  
        // =====================================
        Schema::table('project_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('project_companies', 'billing_method')) {
                $table->enum('billing_method', ['fixed_amount', 'actual_hours'])->default('actual_hours')->after('company_id');
            }
            if (!Schema::hasColumn('project_companies', 'fixed_amount')) {
                $table->decimal('fixed_amount', 10, 2)->nullable()->after('billing_method');
            }
            if (!Schema::hasColumn('project_companies', 'hourly_rate')) {
                $table->decimal('hourly_rate', 8, 2)->nullable()->after('fixed_amount');
            }
            if (!Schema::hasColumn('project_companies', 'notes')) {
                $table->text('notes')->nullable()->after('hourly_rate');
            }
        });

        // =====================================
        // ADD MISSING COLUMNS TO projects TABLE
        // =====================================
        Schema::table('projects', function (Blueprint $table) {
            // Financial fields
            if (!Schema::hasColumn('projects', 'monthly_fee')) {
                $table->decimal('monthly_fee', 10, 2)->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('projects', 'fee_start_date')) {
                $table->date('fee_start_date')->nullable()->after('monthly_fee');
            }
            if (!Schema::hasColumn('projects', 'fee_rollover_enabled')) {
                $table->boolean('fee_rollover_enabled')->default(false)->after('fee_start_date');
            }
            if (!Schema::hasColumn('projects', 'default_hourly_rate')) {
                $table->decimal('default_hourly_rate', 8, 2)->nullable()->after('fee_rollover_enabled');
            }
            if (!Schema::hasColumn('projects', 'main_invoicing_company_id')) {
                $table->foreignId('main_invoicing_company_id')->nullable()->constrained('companies')->after('default_hourly_rate');
            }
            if (!Schema::hasColumn('projects', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(21.00)->after('main_invoicing_company_id');
            }
            if (!Schema::hasColumn('projects', 'notes')) {
                $table->text('notes')->nullable()->after('vat_rate');
            }
            
            // Audit fields
            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->after('notes');
            }
            if (!Schema::hasColumn('projects', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from project_users
        Schema::table('project_users', function (Blueprint $table) {
            $table->dropColumn([
                'role_override',
                'can_edit_fee',
                'can_view_financials', 
                'can_log_time',
                'can_approve_time',
                'added_by',
                'added_at'
            ]);
        });

        // Remove added columns from project_companies
        Schema::table('project_companies', function (Blueprint $table) {
            $table->dropColumn([
                'billing_method',
                'fixed_amount',
                'hourly_rate',
                'notes'
            ]);
        });

        // Remove added columns from projects
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_fee',
                'fee_start_date',
                'fee_rollover_enabled',
                'default_hourly_rate',
                'main_invoicing_company_id',
                'vat_rate',
                'notes',
                'created_by',
                'updated_by'
            ]);
        });
    }
};