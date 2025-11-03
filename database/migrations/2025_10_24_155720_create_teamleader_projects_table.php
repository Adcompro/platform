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
        Schema::create('teamleader_projects', function (Blueprint $table) {
            $table->id();

            // Teamleader references
            $table->string('teamleader_id')->unique()->index();
            $table->string('teamleader_company_id')->index();

            // Progress references (nullable - gekoppeld na sync)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('imported_as_project_id')->nullable()->constrained('projects')->onDelete('set null');

            // Project data
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 50)->index(); // active, on_hold, done, cancelled
            $table->date('starts_on')->nullable();
            $table->date('due_on')->nullable();

            // Budget information
            $table->decimal('budget_amount', 15, 2)->nullable();
            $table->string('budget_currency', 10)->default('EUR');

            // Full Teamleader raw data (voor alle extra velden)
            $table->json('raw_data')->nullable();

            // Import tracking
            $table->boolean('is_imported')->default(false)->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('imported_at')->nullable();

            $table->timestamps();

            // Indexes voor snelle queries
            $table->index(['customer_id', 'is_imported']);
            $table->index(['teamleader_company_id', 'status']);
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_projects');
    }
};
