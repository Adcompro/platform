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
        Schema::create('calendar_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('calendar_event_id')->nullable()->constrained('calendar_events')->onDelete('cascade');
            $table->enum('action', [
                'created', 
                'updated', 
                'deleted', 
                'converted', 
                'cancelled', 
                'synced',
                'attendee_added',
                'attendee_removed',
                'attendee_responded'
            ]);
            $table->string('description');
            $table->json('changes')->nullable(); // Store old and new values
            $table->json('metadata')->nullable(); // Additional data like attendee info
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['calendar_event_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_activities');
    }
};