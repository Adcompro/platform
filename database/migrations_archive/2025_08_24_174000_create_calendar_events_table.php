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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ms_event_id')->unique(); // Microsoft event ID
            $table->string('subject');
            $table->text('body')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('timezone')->default('Europe/Amsterdam');
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->json('attendees')->nullable();
            $table->json('categories')->nullable();
            $table->string('organizer_email')->nullable();
            $table->string('organizer_name')->nullable();
            $table->boolean('is_converted')->default(false); // Track if converted to time entry
            $table->foreignId('time_entry_id')->nullable()->constrained('time_entries')->nullOnDelete();
            $table->json('ms_raw_data')->nullable(); // Store full MS Graph response
            $table->timestamps();
            
            $table->index(['user_id', 'start_datetime']);
            $table->index('is_converted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};