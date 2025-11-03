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
        Schema::create('teamleader_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('teamleader_id')->unique();
            $table->string('teamleader_project_id')->index();
            $table->string('name');
            $table->enum('status', ['open', 'in_progress', 'done', 'on_hold'])->default('open');
            $table->date('starts_on')->nullable();
            $table->date('due_on')->nullable();
            $table->string('invoicing_method')->nullable(); // 'time_and_materials' or 'fixed_price'
            $table->decimal('budget_amount', 10, 2)->nullable();
            $table->integer('allocated_time_seconds')->nullable(); // in seconds
            $table->boolean('is_imported')->default(false);
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->json('raw_data')->nullable(); // Complete API response
            $table->timestamps();

            $table->index('is_imported');
            $table->index(['teamleader_project_id', 'synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_milestones');
    }
};
