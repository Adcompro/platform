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
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('invoice_lines', 'source_type')) {
                $table->string('source_type', 50)->nullable()->after('project_monthly_fee_id');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'group_milestone_id')) {
                $table->unsignedBigInteger('group_milestone_id')->nullable()->after('source_id');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'group_task_id')) {
                $table->unsignedBigInteger('group_task_id')->nullable()->after('group_milestone_id');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'group_subtask_id')) {
                $table->unsignedBigInteger('group_subtask_id')->nullable()->after('group_task_id');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'detailed_description')) {
                $table->text('detailed_description')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'unit')) {
                $table->string('unit', 50)->default('hours')->after('quantity');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->default(0)->after('unit');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('unit_price');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'category')) {
                $table->string('category', 50)->default('work')->after('amount');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'is_billable')) {
                $table->boolean('is_billable')->default(true)->after('category');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'defer_to_next_month')) {
                $table->boolean('defer_to_next_month')->default(false)->after('is_billable');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'is_service_package')) {
                $table->boolean('is_service_package')->default(false)->after('defer_to_next_month');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('is_service_package');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'service_color')) {
                $table->string('service_color', 50)->nullable()->after('service_id');
            }
            
            if (!Schema::hasColumn('invoice_lines', 'metadata')) {
                $table->json('metadata')->nullable()->after('service_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'source_id',
                'group_milestone_id',
                'group_task_id',
                'group_subtask_id',
                'detailed_description',
                'unit',
                'unit_price',
                'amount',
                'category',
                'is_billable',
                'defer_to_next_month',
                'is_service_package',
                'service_id',
                'service_color',
                'metadata'
            ]);
        });
    }
};