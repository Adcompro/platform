<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use Carbon\Carbon;

// Get Twitter source
$source = SocialMediaSource::where('platform', 'twitter')->first();

if (!$source) {
    echo "No Twitter source found\n";
    exit(1);
}

echo "Resetting Twitter Rate Limit Status\n";
echo "====================================\n\n";

// Current status
echo "Current Status:\n";
echo "- Rate Limit Remaining: " . ($source->rate_limit_remaining ?? 'Not set') . "\n";
echo "- Rate Limit Reset: " . ($source->rate_limit_reset ?? 'Not set') . "\n\n";

// Reset the rate limit (for testing purposes)
$source->update([
    'rate_limit_remaining' => null,
    'rate_limit_reset' => null
]);

echo "Rate limit status cleared.\n";
echo "The service will now attempt API calls again.\n\n";

// Check the reset time from the last error
$resetTimestamp = 1757261225; // From the error log
$resetTime = Carbon::createFromTimestamp($resetTimestamp);
$now = Carbon::now();

echo "Based on the last error:\n";
echo "- Rate limit will reset at: " . $resetTime->format('Y-m-d H:i:s') . "\n";

if ($resetTime->isFuture()) {
    $minutesLeft = $now->diffInMinutes($resetTime);
    $secondsLeft = $now->diffInSeconds($resetTime);
    echo "- Time until reset: {$minutesLeft} minutes ({$secondsLeft} seconds)\n";
    echo "\nYou should wait until " . $resetTime->format('H:i:s') . " before trying again.\n";
} else {
    echo "- Rate limit should have already reset!\n";
    echo "- You can try the API again now.\n";
}

echo "\n====================================\n";
echo "Note: Twitter has different rate limits:\n";
echo "- Free tier: Very limited (1-10 requests per 15 min)\n";
echo "- Basic tier ($100/month): 100 requests per 15 min\n";
echo "- Pro tier: Higher limits\n";
echo "\nYour current limit appears to be: 1 request per 15 minutes (Free tier)\n";
echo "Consider upgrading your Twitter API access level for better limits.\n";