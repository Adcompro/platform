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
        // Add teamleader_id to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->string('teamleader_id', 100)->nullable()->unique()->after('id');
            $table->index('teamleader_id');
        });

        // Add teamleader_id to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('teamleader_id', 100)->nullable()->unique()->after('id');
            $table->index('teamleader_id');
        });

        // Add teamleader_id to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->string('teamleader_id', 100)->nullable()->unique()->after('id');
            $table->index('teamleader_id');
        });

        // Add teamleader_id to time_entries table
        Schema::table('time_entries', function (Blueprint $table) {
            $table->string('teamleader_id', 100)->nullable()->unique()->after('id');
            $table->index('teamleader_id');
        });

        // Add teamleader_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('teamleader_id', 100)->nullable()->unique()->after('id');
            $table->index('teamleader_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['teamleader_id']);
            $table->dropColumn('teamleader_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['teamleader_id']);
            $table->dropColumn('teamleader_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['teamleader_id']);
            $table->dropColumn('teamleader_id');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex(['teamleader_id']);
            $table->dropColumn('teamleader_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['teamleader_id']);
            $table->dropColumn('teamleader_id');
        });
    }
};
