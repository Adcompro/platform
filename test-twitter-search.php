<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use App\Services\Social\TwitterService;

// Check Twitter source
$source = SocialMediaSource::where('platform', 'twitter')->first();

if (!$source) {
    echo "No Twitter source found in database\n";
    exit(1);
}

echo "Twitter Search Test\n";
echo "===================\n\n";

// Test the Twitter service
$service = new TwitterService($source);

if (!$service->isConfigured()) {
    echo "Twitter service is not configured\n";
    exit(1);
}

// Test with some keywords
$testKeywords = ['Boeing', 'Dreamliner'];
echo "Searching for keywords: " . implode(', ', $testKeywords) . "\n\n";

try {
    $tweets = $service->searchTweets($testKeywords);
    
    if (empty($tweets)) {
        echo "No tweets found or search failed\n";
        
        // Check Laravel log for errors
        $logFile = storage_path('logs/laravel.log');
        $lastLines = shell_exec("tail -n 20 $logFile | grep -i twitter");
        if ($lastLines) {
            echo "\nRecent Twitter errors in log:\n";
            echo $lastLines;
        }
    } else {
        echo "Found " . count($tweets) . " tweets!\n\n";
        
        // Show first 3 tweets
        $count = 0;
        foreach ($tweets as $tweet) {
            $count++;
            if ($count > 3) break;
            
            echo "Tweet #{$count}:\n";
            echo "- Author: @{$tweet->author_handle} ({$tweet->author_name})\n";
            echo "- Content: " . substr($tweet->content, 0, 100) . "...\n";
            echo "- Posted: {$tweet->published_at}\n";
            echo "- Engagement: {$tweet->likes_count} likes, {$tweet->shares_count} shares\n";
            echo "- URL: {$tweet->post_url}\n\n";
        }
    }
} catch (\Exception $e) {
    echo "Error during search: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}