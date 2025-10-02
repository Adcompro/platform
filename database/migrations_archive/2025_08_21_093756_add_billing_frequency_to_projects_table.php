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
            // Billing frequency fields
            if (!Schema::hasColumn('projects', 'billing_frequency')) {
                $table->enum('billing_frequency', [
                    'monthly',           // Maandelijks factureren
                    'quarterly',         // Per kwartaal
                    'milestone',         // Bij milestone completion
                    'project_completion', // Bij project afronding
                    'custom'            // Custom frequentie
                ])->default('monthly')->after('vat_rate');
            }
            
            // Custom billing interval (voor custom frequency)
            if (!Schema::hasColumn('projects', 'billing_interval_days')) {
                $table->integer('billing_interval_days')->nullable()->after('billing_frequency')
                    ->comment('Number of days for custom billing interval');
            }
            
            // Next billing date
            if (!Schema::hasColumn('projects', 'next_billing_date')) {
                $table->date('next_billing_date')->nullable()->after('billing_interval_days')
                    ->comment('Next scheduled billing date');
            }
            
            // Last billing date
            if (!Schema::hasColumn('projects', 'last_billing_date')) {
                $table->date('last_billing_date')->nullable()->after('next_billing_date')
                    ->comment('Last billing date');
            }
            
            // Template used for this project
            if (!Schema::hasColumn('projects', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('last_billing_date')
                    ->comment('Project template used to create this project');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'billing_frequency',
                'billing_interval_days',
                'next_billing_date',
                'last_billing_date',
                'template_id'
            ]);
        });
    }
};