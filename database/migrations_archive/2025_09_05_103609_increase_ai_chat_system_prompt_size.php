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
        Schema::table('ai_settings', function (Blueprint $table) {
            // Change ai_chat_system_prompt from varchar(1000) to text for unlimited size
            $table->text('ai_chat_system_prompt')->nullable()->change();
            
            // Also increase ai_chat_welcome_message to 500 chars to be safe
            $table->string('ai_chat_welcome_message', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->string('ai_chat_system_prompt', 1000)->nullable()->change();
            $table->string('ai_chat_welcome_message', 255)->nullable()->change();
        });
    }
};