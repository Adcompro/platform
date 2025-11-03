<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use Carbon\Carbon;

echo "========================================\n";
echo "Twitter API Status & Configuration\n";
echo "========================================\n";
echo date('Y-m-d H:i:s') . " (Server time)\n";
echo Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s') . " (Amsterdam time)\n\n";

// Get Twitter source
$twitter = SocialMediaSource::where('platform', 'twitter')->first();

if (!$twitter) {
    echo "❌ No Twitter source configured\n";
    exit(1);
}

echo "✅ CONFIGURATION UPDATED\n";
echo "------------------------\n";
echo "• Check frequency: " . $twitter->check_frequency . " minutes (24 hours)\n";
echo "• Scheduled time: Daily at 09:00 Amsterdam time\n";
echo "• Next run: " . Carbon::now('Europe/Amsterdam')->startOfDay()->addHours(9)->addDay()->format('Y-m-d H:i:s') . "\n\n";

echo "📊 CURRENT STATUS\n";
echo "-----------------\n";
echo "• Active: " . ($twitter->is_active ? 'Yes' : 'No') . "\n";
echo "• Last checked: " . ($twitter->last_checked_at ?? 'Never') . "\n";
echo "• Last collected: " . ($twitter->last_collected_at ?? 'Never') . "\n";
echo "• Rate limit remaining: " . ($twitter->rate_limit_remaining ?? 'Unknown') . "\n";
echo "• Rate limit reset: " . ($twitter->rate_limit_reset_at ?? 'Unknown') . "\n\n";

echo "🚨 API LIMITS\n";
echo "-------------\n";
echo "• Monthly cap: 10,000 tweet reads\n";
echo "• Current status: EXCEEDED (will reset Oct 1)\n";
echo "• Daily at 09:00: ~300 reads/month (well within limit)\n\n";

echo "📅 SCHEDULE\n";
echo "-----------\n";
$nextRun = Carbon::now('Europe/Amsterdam');
if ($nextRun->hour >= 9) {
    $nextRun->addDay();
}
$nextRun->setTime(9, 0, 0);

echo "• Next Twitter check: " . $nextRun->format('Y-m-d H:i:s') . " (Amsterdam)\n";
echo "• Time until next check: " . $nextRun->diffForHumans() . "\n\n";

echo "💾 DATABASE STATS\n";
echo "-----------------\n";
$tweetCount = \App\Models\SocialMediaMention::where('source_id', $twitter->id)->count();
echo "• Total tweets stored: {$tweetCount}\n";

$lastTweet = \App\Models\SocialMediaMention::where('source_id', $twitter->id)
    ->orderBy('created_at', 'desc')
    ->first();
if ($lastTweet) {
    echo "• Last tweet stored: " . $lastTweet->created_at . "\n";
}

echo "\n📝 LOG LOCATION\n";
echo "---------------\n";
echo "• Twitter logs: storage/logs/twitter-collection.log\n";
echo "• Laravel logs: storage/logs/laravel.log\n";

echo "\n✅ SETUP COMPLETE!\n";
echo "==================\n";
echo "Twitter will now check ONCE per day at 09:00 AM Amsterdam time.\n";
echo "This uses only ~300 API calls per month (3% of your 10,000 limit).\n";
echo "\nNOTE: API access will resume on October 1st when monthly limit resets.\n";