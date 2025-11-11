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
        Schema::table('project_additional_costs', function (Blueprint $table) {
            // Calculation fields voor flexibele berekeningen
            $table->decimal('hours', 8, 2)->nullable()->after('amount');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('hours');
            $table->decimal('quantity', 10, 2)->default(1.00)->after('hourly_rate');
            $table->string('unit', 50)->default('piece')->after('quantity');
            $table->enum('calculation_type', ['fixed_amount', 'hourly_rate', 'quantity_based'])
                  ->default('fixed_amount')
                  ->after('unit');

            // Recurring settings
            $table->tinyInteger('recurring_day_of_month')->nullable()->after('end_date');

            // Monthly variations voor variabele bedragen per maand
            $table->json('monthly_variations')->nullable()->after('recurring_day_of_month');

            // Index voor betere performance
            $table->index(['project_id', 'cost_type', 'is_active']);
            $table->index(['project_id', 'fee_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_additional_costs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'cost_type', 'is_active']);
            $table->dropIndex(['project_id', 'fee_type']);

            $table->dropColumn([
                'hours',
                'hourly_rate',
                'quantity',
                'unit',
                'calculation_type',
                'recurring_day_of_month',
                'monthly_variations',
            ]);
        });
    }
};
