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
            // Track if this time entry was deferred and when
            $table->boolean('was_deferred')->default(false)->after('invoiced_modified_by');
            $table->datetime('deferred_at')->nullable()->after('was_deferred');
            $table->bigInteger('deferred_by')->unsigned()->nullable()->after('deferred_at');
            $table->text('defer_reason')->nullable()->after('deferred_by');

            // Foreign key for deferred_by
            $table->foreign('deferred_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['deferred_by']);
            $table->dropColumn([
                'was_deferred',
                'deferred_at',
                'deferred_by',
                'defer_reason'
            ]);
        });
    }
};