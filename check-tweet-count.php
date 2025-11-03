<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaMention;
use App\Models\SocialMediaSource;
use Illuminate\Support\Facades\DB;

echo "Twitter Database Analysis\n";
echo "=========================\n\n";

// Get Twitter source
$twitterSource = SocialMediaSource::where('platform', 'twitter')->first();

echo "1. Total Twitter mentions in database:\n";
echo "--------------------------------------\n";

if ($twitterSource) {
    $totalTweets = SocialMediaMention::where('source_id', $twitterSource->id)->count();
    echo "Total tweets stored: {$totalTweets}\n\n";
    
    // Get tweets per day
    echo "2. Tweets stored per day:\n";
    echo "-------------------------\n";
    $tweetsPerDay = SocialMediaMention::where('source_id', $twitterSource->id)
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->limit(10)
        ->get();
    
    foreach ($tweetsPerDay as $day) {
        echo "{$day->date}: {$day->count} tweets\n";
    }
    
    // Get unique platform_post_ids (actual unique tweets)
    echo "\n3. Unique tweets (by platform_post_id):\n";
    echo "----------------------------------------\n";
    $uniqueTweets = SocialMediaMention::where('source_id', $twitterSource->id)
        ->distinct('platform_post_id')
        ->count('platform_post_id');
    echo "Unique tweets: {$uniqueTweets}\n";
    
    // Get first and last tweet dates
    echo "\n4. Date range of stored tweets:\n";
    echo "--------------------------------\n";
    $firstTweet = SocialMediaMention::where('source_id', $twitterSource->id)
        ->orderBy('created_at', 'asc')
        ->first();
    $lastTweet = SocialMediaMention::where('source_id', $twitterSource->id)
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($firstTweet) {
        echo "First tweet stored: {$firstTweet->created_at}\n";
    }
    if ($lastTweet) {
        echo "Last tweet stored: {$lastTweet->created_at}\n";
    }
    
    // Check for duplicates
    echo "\n5. Duplicate analysis:\n";
    echo "----------------------\n";
    $duplicates = DB::table('social_media_mentions')
        ->where('source_id', $twitterSource->id)
        ->select('platform_post_id', DB::raw('COUNT(*) as count'))
        ->groupBy('platform_post_id')
        ->having('count', '>', 1)
        ->get();
    
    echo "Posts with duplicates: " . $duplicates->count() . "\n";
    if ($duplicates->count() > 0) {
        echo "Total duplicate entries: " . $duplicates->sum('count') . "\n";
    }
} else {
    echo "No Twitter source found in database\n";
}

echo "\n6. ALL social media mentions (all platforms):\n";
echo "----------------------------------------------\n";
$allMentions = SocialMediaMention::count();
echo "Total mentions across all platforms: {$allMentions}\n";

// Group by source
$mentionsBySource = DB::table('social_media_mentions')
    ->join('social_media_sources', 'social_media_mentions.source_id', '=', 'social_media_sources.id')
    ->select('social_media_sources.platform', 'social_media_sources.name', DB::raw('COUNT(*) as count'))
    ->groupBy('social_media_sources.platform', 'social_media_sources.name')
    ->get();

foreach ($mentionsBySource as $source) {
    echo "- {$source->platform} ({$source->name}): {$source->count} mentions\n";
}

echo "\n7. Twitter API usage estimate:\n";
echo "-------------------------------\n";
echo "If you made API calls but got few/no results, the 10,000 reads were likely used for:\n";
echo "• Testing and debugging API connections\n";
echo "• Failed searches that still count against quota\n";
echo "• Rate limit checks and connection tests\n";
echo "• Each search counts even if it returns 0 results\n";
echo "• Automated checks running every few minutes\n\n";

// Check last_collected_at to see collection frequency
if ($twitterSource && $twitterSource->last_collected_at) {
    echo "Last collection attempt: {$twitterSource->last_collected_at}\n";
    echo "Check frequency setting: {$twitterSource->check_frequency} minutes\n";
    
    // Calculate theoretical API calls
    $daysSinceStart = 7; // Assuming started using on Sept 1st
    $checksPerDay = (24 * 60) / $twitterSource->check_frequency;
    $estimatedCalls = $daysSinceStart * $checksPerDay;
    echo "\nEstimated API calls (if running continuously):\n";
    echo "- Checks per day: " . round($checksPerDay) . "\n";
    echo "- Total estimated calls: " . round($estimatedCalls) . "\n";
}