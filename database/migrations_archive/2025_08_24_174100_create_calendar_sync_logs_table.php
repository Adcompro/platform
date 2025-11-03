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
        Schema::create('calendar_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('sync_type', ['manual', 'automatic', 'webhook']);
            $table->enum('status', ['started', 'completed', 'failed']);
            $table->integer('events_synced')->default(0);
            $table->integer('events_created')->default(0);
            $table->integer('events_updated')->default(0);
            $table->integer('events_deleted')->default(0);
            $table->dateTime('sync_from')->nullable();
            $table->dateTime('sync_to')->nullable();
            $table->text('error_message')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_sync_logs');
    }
};