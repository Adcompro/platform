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
            // Remove code editor related columns
            $table->dropColumn('custom_html');
            $table->dropColumn('custom_css');
            $table->dropColumn('editor_mode');
            $table->dropColumn('use_custom_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {
            // Re-add the columns if we need to rollback
            $table->text('custom_html')->nullable()->after('block_positions');
            $table->text('custom_css')->nullable()->after('custom_html');
            $table->enum('editor_mode', ['visual', 'code'])->default('visual')->after('custom_css');
            $table->boolean('use_custom_code')->default(false)->after('editor_mode');
        });
    }
};