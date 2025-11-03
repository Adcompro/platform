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
        Schema::create('sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_type'); // 'contact_companies', 'global_sync', etc.
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('total_items')->default(0); // Totaal aantal te verwerken items
            $table->integer('processed_items')->default(0); // Aantal verwerkte items
            $table->integer('successful_items')->default(0); // Succesvol verwerkt
            $table->integer('failed_items')->default(0); // Gefaald
            $table->text('current_item')->nullable(); // Huidige item naam
            $table->text('error_message')->nullable(); // Laatste error
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'job_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_jobs');
    }
};
