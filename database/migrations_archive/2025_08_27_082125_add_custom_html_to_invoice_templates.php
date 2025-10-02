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
        Schema::table('invoice_templates', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('invoice_templates', 'custom_html')) {
                $table->longText('custom_html')->nullable()->after('block_positions');
            }
            if (!Schema::hasColumn('invoice_templates', 'custom_css')) {
                $table->longText('custom_css')->nullable()->after('custom_html');
            }
            if (!Schema::hasColumn('invoice_templates', 'editor_mode')) {
                $table->enum('editor_mode', ['visual', 'code'])->default('visual')->after('custom_css');
            }
            if (!Schema::hasColumn('invoice_templates', 'use_custom_code')) {
                $table->boolean('use_custom_code')->default(false)->after('editor_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn(['custom_html', 'custom_css', 'editor_mode', 'use_custom_code']);
        });
    }
};
