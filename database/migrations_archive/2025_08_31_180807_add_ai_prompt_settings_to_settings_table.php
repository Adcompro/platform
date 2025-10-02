<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default AI prompt settings
        $prompts = [
            [
                'key' => 'ai_invoice_system_prompt',
                'value' => 'You are an expert at analyzing time entries and creating comprehensive invoice descriptions for clients. Your task is to intelligently consolidate similar activities while preserving ALL important details. IMPORTANT: Always create descriptions in ENGLISH for international business compatibility. Focus on deliverables and value provided to the client. Keep all essential information - it\'s better to have multiple detailed lines than to lose important context.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_consolidation_instructions',
                'value' => '1. Analyze ALL time entry descriptions and group truly similar activities
2. Create comprehensive descriptions that include ALL important work performed
3. Use multiple bullet points or lines when different types of work were done
4. Keep specific technical details, feature names, bug fixes, and deliverables
5. For repetitive tasks (like daily meetings), combine them but mention frequency
6. NEVER lose important information - when in doubt, keep it as separate items
7. Format descriptions professionally for client invoices',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_description_prompt',
                'value' => 'Generate a professional invoice description for:
Project: {PROJECT_NAME}
Period: {PERIOD}
Work Summary: {WORK_SUMMARY}

Provide a clear, concise description (max 100 words) that highlights the value delivered.
IMPORTANT: Always respond in ENGLISH, regardless of the project name or input language.
Format: Return a JSON object with key \'invoice_description\' containing the English description.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_output_language',
                'value' => 'en', // Default to English, can be changed to 'nl', 'auto', etc.
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_max_description_words',
                'value' => '100',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_include_technical_details',
                'value' => 'true',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_invoice_group_similar_threshold',
                'value' => '0.8', // Similarity threshold for grouping (0-1)
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('settings')->insert($prompts);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'ai_invoice_system_prompt',
            'ai_invoice_consolidation_instructions',
            'ai_invoice_description_prompt',
            'ai_invoice_output_language',
            'ai_invoice_max_description_words',
            'ai_invoice_include_technical_details',
            'ai_invoice_group_similar_threshold'
        ])->delete();
    }
};