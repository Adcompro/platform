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
        Schema::table('projects', function (Blueprint $table) {
            // Multi-tenant support
            if (!Schema::hasColumn('projects', 'company_id')) {
                $table->foreignId('company_id')->after('id')->constrained()->onDelete('cascade');
            }
            
            // Financial fields
            if (!Schema::hasColumn('projects', 'monthly_fee')) {
                $table->decimal('monthly_fee', 10, 2)->nullable()->after('end_date');
            }
            
            if (!Schema::hasColumn('projects', 'fee_start_date')) {
                $table->date('fee_start_date')->nullable()->after('monthly_fee');
            }
            
            if (!Schema::hasColumn('projects', 'fee_rollover_enabled')) {
                $table->boolean('fee_rollover_enabled')->default(true)->after('fee_start_date');
            }
            
            if (!Schema::hasColumn('projects', 'default_hourly_rate')) {
                $table->decimal('default_hourly_rate', 8, 2)->nullable()->after('fee_rollover_enabled');
            }
            
            if (!Schema::hasColumn('projects', 'main_invoicing_company_id')) {
                $table->foreignId('main_invoicing_company_id')->nullable()->after('default_hourly_rate')->constrained('companies')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('projects', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(21.00)->after('main_invoicing_company_id');
            }
            
            // Notes field
            if (!Schema::hasColumn('projects', 'notes')) {
                $table->text('notes')->nullable()->after('vat_rate');
            }
            
            // Audit trail fields
            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('projects', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('projects', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->onDelete('set null');
            }
            
            // Soft deletes (for future use)
            if (!Schema::hasColumn('projects', 'deleted_at')) {
                $table->softDeletes()->after('deleted_by');
            }
        });
        
        // Update status column to use enum if it exists but is wrong type
        if (Schema::hasColumn('projects', 'status')) {
            // Check current status column and update if needed
            Schema::table('projects', function (Blueprint $table) {
                $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'on_hold'])->default('draft')->change();
            });
        } else {
            Schema::table('projects', function (Blueprint $table) {
                $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'on_hold'])->default('draft')->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Remove foreign key constraints first
            if (Schema::hasColumn('projects', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            
            if (Schema::hasColumn('projects', 'main_invoicing_company_id')) {
                $table->dropForeign(['main_invoicing_company_id']);
                $table->dropColumn('main_invoicing_company_id');
            }
            
            if (Schema::hasColumn('projects', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('projects', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            if (Schema::hasColumn('projects', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
            
            // Remove other columns
            $columnsToRemove = [
                'monthly_fee',
                'fee_start_date', 
                'fee_rollover_enabled',
                'default_hourly_rate',
                'vat_rate',
                'notes',
                'deleted_at'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};