<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MediaSource;
use App\Models\RssFeedCache;

echo "========================================\n";
echo "RSS Feed Configuration Status\n";
echo "========================================\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Stats
$totalFeeds = MediaSource::count();
$activeFeeds = MediaSource::where('is_active', true)->count();
$inactiveFeeds = MediaSource::where('is_active', false)->count();
$articlesToday = RssFeedCache::whereDate('created_at', today())->count();
$articlesTotal = RssFeedCache::count();

echo "📊 STATISTICS\n";
echo "-------------\n";
echo "• Total RSS feeds: {$totalFeeds}\n";
echo "• Active feeds: {$activeFeeds}\n";
echo "• Inactive feeds: {$inactiveFeeds}\n";
echo "• Articles today: {$articlesToday}\n";
echo "• Total articles cached: {$articlesTotal}\n\n";

echo "📰 FEED CATEGORIES\n";
echo "------------------\n";
$categories = MediaSource::selectRaw('category, COUNT(*) as count, SUM(is_active) as active')
    ->groupBy('category')
    ->get();

foreach ($categories as $cat) {
    echo "• {$cat->category}: {$cat->count} feeds ({$cat->active} active)\n";
}

echo "\n⏰ CHECK FREQUENCIES\n";
echo "--------------------\n";
$frequencies = MediaSource::where('is_active', true)
    ->selectRaw('check_frequency, COUNT(*) as count')
    ->groupBy('check_frequency')
    ->orderBy('check_frequency')
    ->get();

foreach ($frequencies as $freq) {
    $label = match(true) {
        $freq->check_frequency <= 30 => "Every {$freq->check_frequency} minutes",
        $freq->check_frequency == 60 => "Every hour",
        $freq->check_frequency == 1440 => "Once per day",
        default => "Every " . round($freq->check_frequency / 60, 1) . " hours"
    };
    echo "• {$label}: {$freq->count} feeds\n";
}

echo "\n🔄 RECENT ACTIVITY\n";
echo "------------------\n";
$recentFeeds = MediaSource::where('is_active', true)
    ->whereNotNull('last_checked_at')
    ->orderBy('last_checked_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentFeeds as $feed) {
    echo "• {$feed->name}: " . $feed->last_checked_at->diffForHumans() . "\n";
}

echo "\n✅ NEW FEATURES\n";
echo "---------------\n";
echo "• RSS Feed Settings page: /settings/rss-feeds\n";
echo "• Add/Edit/Delete RSS feeds\n";
echo "• Test feed functionality\n";
echo "• Bulk actions (activate/deactivate/delete)\n";
echo "• Category and status filtering\n";
echo "• Real-time toggle for active/inactive\n\n";

echo "📋 HOW TO ACCESS\n";
echo "----------------\n";
echo "1. Login as admin or super_admin\n";
echo "2. Go to Settings → RSS Feeds in the menu\n";
echo "3. Or navigate to: " . url('/settings/rss-feeds') . "\n\n";

echo "🔧 SCHEDULE\n";
echo "-----------\n";
echo "RSS feeds are collected every 30 minutes via:\n";
echo "• Command: php artisan media:collect-feeds\n";
echo "• Schedule: routes/console.php\n";
echo "• Log: storage/logs/laravel.log\n";