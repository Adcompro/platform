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
            // Track if entry's invoice has been finalized
            $table->boolean('is_finalized')->default(false)->after('is_invoiced');
            $table->timestamp('finalized_at')->nullable()->after('is_finalized');
            $table->string('final_invoice_number')->nullable()->after('finalized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['is_finalized', 'finalized_at', 'final_invoice_number']);
        });
    }
};