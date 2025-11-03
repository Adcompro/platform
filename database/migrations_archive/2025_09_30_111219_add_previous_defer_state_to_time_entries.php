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
            // Track if entry was deferred before being included in invoice
            $table->boolean('was_previously_deferred')->default(false)->after('defer_reason');
            $table->timestamp('previous_deferred_at')->nullable()->after('was_previously_deferred');
            $table->unsignedBigInteger('previous_deferred_by')->nullable()->after('previous_deferred_at');
            $table->text('previous_defer_reason')->nullable()->after('previous_deferred_by');

            // Add foreign key for previous_deferred_by
            $table->foreign('previous_deferred_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['previous_deferred_by']);
            $table->dropColumn(['was_previously_deferred', 'previous_deferred_at', 'previous_deferred_by', 'previous_defer_reason']);
        });
    }
};