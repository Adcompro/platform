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
            // Add missing columns
            if (!Schema::hasColumn('invoices', 'billing_type')) {
                $table->string('billing_type', 50)->default('monthly')->after('status');
            }
            
            if (!Schema::hasColumn('invoices', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('customer_id');
                $table->foreign('created_by')->references('id')->on('users');
            }
            
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('invoice_date');
            }
            
            if (!Schema::hasColumn('invoices', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(21.00)->after('period_end');
            }
            
            if (!Schema::hasColumn('invoices', 'previous_month_remaining')) {
                $table->decimal('previous_month_remaining', 12, 2)->default(0)->after('vat_rate');
            }
            
            if (!Schema::hasColumn('invoices', 'monthly_budget')) {
                $table->decimal('monthly_budget', 12, 2)->default(0)->after('previous_month_remaining');
            }
            
            if (!Schema::hasColumn('invoices', 'total_budget')) {
                $table->decimal('total_budget', 12, 2)->default(0)->after('monthly_budget');
            }
            
            if (!Schema::hasColumn('invoices', 'next_month_rollover')) {
                $table->decimal('next_month_rollover', 12, 2)->default(0)->after('total_budget');
            }
            
            if (!Schema::hasColumn('invoices', 'work_amount')) {
                $table->decimal('work_amount', 12, 2)->default(0)->after('next_month_rollover');
            }
            
            if (!Schema::hasColumn('invoices', 'service_amount')) {
                $table->decimal('service_amount', 12, 2)->default(0)->after('work_amount');
            }
            
            if (!Schema::hasColumn('invoices', 'additional_costs')) {
                $table->decimal('additional_costs', 12, 2)->default(0)->after('service_amount');
            }
            
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('additional_costs');
            }
            
            if (!Schema::hasColumn('invoices', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('finalized_at');
            }
            
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('sent_at');
            }
            
            if (!Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'billing_type',
                'created_by',
                'due_date',
                'vat_rate',
                'previous_month_remaining',
                'monthly_budget',
                'total_budget',
                'next_month_rollover',
                'work_amount',
                'service_amount',
                'additional_costs',
                'subtotal',
                'sent_at',
                'paid_at',
                'paid_amount'
            ]);
        });
    }
};