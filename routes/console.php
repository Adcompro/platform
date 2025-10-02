<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Calendar sync scheduling
// Run calendar sync every hour for all users
Schedule::command('calendar:sync --user=all')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Log::error('Calendar sync failed at ' . now()->format('Y-m-d H:i:s'));
    })
    ->onSuccess(function () {
        \Log::info('Calendar sync completed successfully at ' . now()->format('Y-m-d H:i:s'));
    });

// Alternative: Run every 15 minutes during business hours (8:00 - 18:00)
// Schedule::command('calendar:sync --user=all')
//     ->everyFifteenMinutes()
//     ->between('8:00', '18:00')
//     ->weekdays()
//     ->withoutOverlapping()
//     ->runInBackground();

// Media monitoring RSS feed collection
// Run every 30 minutes to check for new articles
Schedule::command('media:collect-feeds')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Log::error('Media feed collection failed at ' . now()->format('Y-m-d H:i:s'));
    })
    ->onSuccess(function () {
        \Log::info('Media feed collection completed at ' . now()->format('Y-m-d H:i:s'));
    });

// Process media monitoring email digests
// Hourly digest at :05 minutes past the hour
Schedule::command('media:send-digest hourly')
    ->hourlyAt(5)
    ->runInBackground();

// Daily digest at 8:00 AM
Schedule::command('media:send-digest daily')
    ->dailyAt('08:00')
    ->runInBackground();

// Twitter API Collection - LIMITED TO 1x PER DAY
// Due to API limits (10,000 reads/month), we only check once daily
Schedule::command('social:collect --source=twitter')
    ->dailyAt('09:00')
    ->timezone('Europe/Amsterdam')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/twitter-collection.log'))
    ->onFailure(function () {
        \Log::error('Twitter collection failed at ' . now()->format('Y-m-d H:i:s'));
    })
    ->onSuccess(function () {
        \Log::info('Twitter collection completed at ' . now()->format('Y-m-d H:i:s'));
    });
