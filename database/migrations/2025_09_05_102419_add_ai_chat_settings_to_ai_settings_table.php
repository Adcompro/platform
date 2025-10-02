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
            // AI Chat specific settings
            $table->string('ai_chat_system_prompt')->nullable()->after('ai_learning_enabled');
            $table->integer('ai_chat_max_tokens')->default(2000)->after('ai_chat_system_prompt');
            $table->decimal('ai_chat_temperature', 3, 2)->default(0.7)->after('ai_chat_max_tokens');
            $table->integer('ai_chat_history_limit')->default(20)->after('ai_chat_temperature');
            $table->boolean('ai_chat_show_context')->default(true)->after('ai_chat_history_limit');
            $table->boolean('ai_chat_allow_file_analysis')->default(false)->after('ai_chat_show_context');
            $table->json('ai_chat_quick_actions')->nullable()->after('ai_chat_allow_file_analysis');
            $table->string('ai_chat_welcome_message')->nullable()->after('ai_chat_quick_actions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_chat_system_prompt',
                'ai_chat_max_tokens',
                'ai_chat_temperature',
                'ai_chat_history_limit',
                'ai_chat_show_context',
                'ai_chat_allow_file_analysis',
                'ai_chat_quick_actions',
                'ai_chat_welcome_message'
            ]);
        });
    }
};