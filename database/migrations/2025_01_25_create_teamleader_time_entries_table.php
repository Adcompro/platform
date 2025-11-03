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
        Schema::create('teamleader_time_entries', function (Blueprint $table) {
            $table->id();
            $table->string('teamleader_id')->unique();
            $table->string('teamleader_project_id')->nullable()->index();
            $table->string('teamleader_user_id')->nullable()->index(); // Teamleader user who tracked time
            $table->date('date');
            $table->integer('duration_seconds'); // Duration in seconds
            $table->text('description')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable(); // Hourly rate from Teamleader
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_imported')->default(false);
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->json('raw_data')->nullable(); // Complete API response
            $table->timestamps();

            $table->index('is_imported');
            $table->index(['teamleader_project_id', 'synced_at']);
            $table->index(['date', 'teamleader_project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teamleader_time_entries');
    }
};
