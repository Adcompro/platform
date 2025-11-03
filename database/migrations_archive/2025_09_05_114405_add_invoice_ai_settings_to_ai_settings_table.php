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
            // Invoice AI Settings
            $table->boolean('ai_invoice_enabled')->default(true)->after('ai_time_entry_history_days');
            $table->text('ai_invoice_system_prompt')->nullable()->after('ai_invoice_enabled');
            $table->text('ai_invoice_consolidation_instructions')->nullable()->after('ai_invoice_system_prompt');
            $table->text('ai_invoice_description_prompt')->nullable()->after('ai_invoice_consolidation_instructions');
            $table->string('ai_invoice_output_language', 10)->default('nl')->after('ai_invoice_description_prompt');
            $table->integer('ai_invoice_max_description_words')->default(100)->after('ai_invoice_output_language');
            $table->boolean('ai_invoice_include_technical_details')->default(true)->after('ai_invoice_max_description_words');
            $table->decimal('ai_invoice_group_similar_threshold', 3, 2)->default(0.80)->after('ai_invoice_include_technical_details');
            
            // Communication agency specific
            $table->boolean('ai_invoice_bundle_press_releases')->default(true)->after('ai_invoice_group_similar_threshold');
            $table->boolean('ai_invoice_list_all_media')->default(true)->after('ai_invoice_bundle_press_releases');
            $table->boolean('ai_invoice_group_by_activity_type')->default(true)->after('ai_invoice_list_all_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_invoice_enabled',
                'ai_invoice_system_prompt',
                'ai_invoice_consolidation_instructions',
                'ai_invoice_description_prompt',
                'ai_invoice_output_language',
                'ai_invoice_max_description_words',
                'ai_invoice_include_technical_details',
                'ai_invoice_group_similar_threshold',
                'ai_invoice_bundle_press_releases',
                'ai_invoice_list_all_media',
                'ai_invoice_group_by_activity_type'
            ]);
        });
    }
};
