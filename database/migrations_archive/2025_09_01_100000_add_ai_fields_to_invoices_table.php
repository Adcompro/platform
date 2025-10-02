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
        Schema::table('invoices', function (Blueprint $table) {
            // AI generation tracking
            $table->boolean('ai_generated')->default(false)->after('paid_amount');
            $table->decimal('ai_confidence_score', 3, 2)->nullable()->after('ai_generated');
            $table->timestamp('ai_generated_at')->nullable()->after('ai_confidence_score');
            
            // Activity report data storage
            $table->json('activity_report_data')->nullable()->after('ai_generated_at');
            
            // Fee balance tracking voor activity report
            $table->decimal('fee_balance_previous', 10, 2)->default(0)->after('activity_report_data');
            $table->decimal('fee_balance_current', 10, 2)->default(0)->after('fee_balance_previous');
            $table->decimal('fee_performed', 10, 2)->default(0)->after('fee_balance_current');
            $table->decimal('fee_balance_new', 10, 2)->default(0)->after('fee_performed');
            
            // Additional costs breakdown
            $table->decimal('additional_costs_in_fee', 10, 2)->default(0)->after('additional_costs');
            $table->decimal('additional_costs_outside_fee', 10, 2)->default(0)->after('additional_costs_in_fee');
            
            // Index voor snelle queries
            $table->index('ai_generated');
            $table->index(['project_id', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['ai_generated']);
            $table->dropIndex(['project_id', 'period_start', 'period_end']);
            
            $table->dropColumn([
                'ai_generated',
                'ai_confidence_score',
                'ai_generated_at',
                'activity_report_data',
                'fee_balance_previous',
                'fee_balance_current',
                'fee_performed',
                'fee_balance_new',
                'additional_costs_in_fee',
                'additional_costs_outside_fee'
            ]);
        });
    }
};