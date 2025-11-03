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
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->foreign('invoice_line_id')->references('id')->on('invoice_lines');
            $table->index(['invoice_id', 'is_invoiced']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['invoice_line_id']);
            $table->dropIndex(['invoice_id', 'is_invoiced']);
        });
    }
};