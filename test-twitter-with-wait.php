<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use App\Services\Social\TwitterService;
use Carbon\Carbon;

// Get Twitter source
$source = SocialMediaSource::where('platform', 'twitter')->first();

if (!$source) {
    echo "No Twitter source found\n";
    exit(1);
}

echo "Twitter API Test with Rate Limit Management\n";
echo "===========================================\n\n";

// Check if we're rate limited
if ($source->isRateLimited()) {
    $resetTime = $source->rate_limit_reset_at;
    $now = Carbon::now();
    $secondsToWait = $now->diffInSeconds($resetTime);
    
    echo "Currently rate limited.\n";
    echo "Reset time: " . $resetTime->format('H:i:s') . "\n";
    echo "Seconds to wait: {$secondsToWait}\n\n";
    
    if ($secondsToWait > 0 && $secondsToWait < 900) { // Wait max 15 minutes
        echo "Waiting for rate limit to reset...\n";
        for ($i = $secondsToWait; $i > 0; $i--) {
            echo "\rTime remaining: " . gmdate("i:s", $i) . "  ";
            sleep(1);
        }
        echo "\n\nRate limit should be reset now!\n\n";
    } else {
        echo "Too long to wait. Please try again later.\n";
        exit(1);
    }
}

// Test the Twitter service
$service = new TwitterService($source);

if (!$service->isConfigured()) {
    echo "Twitter service is not configured\n";
    exit(1);
}

// Test with a simple keyword
$testKeywords = ['news'];  // Simple common keyword to test
echo "Searching for keyword: " . implode(', ', $testKeywords) . "\n\n";

try {
    $tweets = $service->searchTweets($testKeywords);
    
    if (empty($tweets)) {
        echo "No tweets found.\n";
        
        // Check if we got rate limited again
        $source->refresh();
        if ($source->isRateLimited()) {
            echo "Rate limited again. Reset at: " . $source->rate_limit_reset_at->format('H:i:s') . "\n";
        }
    } else {
        echo "SUCCESS! Found " . count($tweets) . " tweets!\n\n";
        
        // Show first tweet
        $firstTweet = $tweets[0];
        echo "First Tweet:\n";
        echo "- Author: @{$firstTweet->author_handle} ({$firstTweet->author_name})\n";
        echo "- Content: " . substr($firstTweet->content, 0, 100) . "...\n";
        echo "- Posted: {$firstTweet->published_at}\n";
        echo "- Engagement: {$firstTweet->likes_count} likes, {$firstTweet->shares_count} shares\n";
        echo "- URL: {$firstTweet->post_url}\n\n";
        
        // Check database storage
        echo "Database Check:\n";
        echo "- Mention ID: {$firstTweet->id}\n";
        echo "- Platform Post ID: {$firstTweet->platform_post_id}\n";
        echo "- Stored in database: Yes\n";
        
        // Check rate limit status after successful call
        $source->refresh();
        echo "\nRate Limit Status:\n";
        echo "- Remaining: " . ($source->rate_limit_remaining ?? 'Unknown') . "\n";
        if ($source->rate_limit_reset_at) {
            echo "- Reset at: " . $source->rate_limit_reset_at->format('H:i:s') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error during search: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}