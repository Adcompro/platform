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
        Schema::table('theme_settings', function (Blueprint $table) {
            // Check if fields don't exist before adding them
            if (!\Schema::hasColumn('theme_settings', 'header_padding')) {
                $table->string('header_padding')->default('py-4')->after('border_radius');
            }
            if (!\Schema::hasColumn('theme_settings', 'card_padding')) {
                $table->string('card_padding')->default('p-4')->after('header_padding');
            }
            // border_radius and button_padding should already exist from previous migrations
            // transition_speed should already exist from previous migrations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'header_padding',
                'card_padding'
            ]);
        });
    }
};
