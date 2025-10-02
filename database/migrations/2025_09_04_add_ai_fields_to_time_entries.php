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
        Schema::table('time_entries', function (Blueprint $table) {
            // AI normalization fields
            $table->text('original_description')->nullable()->after('description');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('original_description');
            $table->text('ai_improved_description')->nullable()->after('ai_confidence');
            $table->boolean('ai_suggestion_used')->default(false)->after('ai_improved_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn([
                'original_description',
                'ai_confidence',
                'ai_improved_description',
                'ai_suggestion_used'
            ]);
        });
    }
};