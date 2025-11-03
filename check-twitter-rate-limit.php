<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

// Get Twitter source
$source = SocialMediaSource::where('platform', 'twitter')->first();

if (!$source) {
    echo "No Twitter source found\n";
    exit(1);
}

echo "Twitter Rate Limit Check\n";
echo "========================\n\n";

// Check current rate limit status in database
echo "Database Rate Limit Info:\n";
echo "- Rate Limit Remaining: " . ($source->rate_limit_remaining ?? 'Not set') . "\n";
echo "- Rate Limit Reset: " . ($source->rate_limit_reset ?? 'Not set') . "\n";

if ($source->rate_limit_reset) {
    $resetTime = \Carbon\Carbon::parse($source->rate_limit_reset);
    $now = \Carbon\Carbon::now();
    
    if ($resetTime->isFuture()) {
        $minutesLeft = $now->diffInMinutes($resetTime);
        echo "- Minutes until reset: {$minutesLeft}\n";
    } else {
        echo "- Rate limit should have reset\n";
    }
}

// Check if rate limited
echo "- Is Rate Limited: " . ($source->isRateLimited() ? 'Yes' : 'No') . "\n\n";

// Get bearer token
$credentials = $source->api_credentials;
$bearerToken = null;

if (isset($credentials['bearer_token'])) {
    $bearerToken = str_starts_with($credentials['bearer_token'], 'eyJ') ? 
        Crypt::decryptString($credentials['bearer_token']) : 
        $credentials['bearer_token'];
}

if (!$bearerToken) {
    echo "No bearer token found\n";
    exit(1);
}

// Check rate limit directly with Twitter API
echo "Checking Twitter API Rate Limit Status:\n";

try {
    // Use the application rate limit status endpoint
    $response = Http::withToken($bearerToken)
        ->get('https://api.twitter.com/1.1/application/rate_limit_status.json', [
            'resources' => 'search'
        ]);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if (isset($data['resources']['search']['/search/tweets'])) {
            $searchLimit = $data['resources']['search']['/search/tweets'];
            echo "- Search Tweets Endpoint:\n";
            echo "  - Limit: {$searchLimit['limit']}\n";
            echo "  - Remaining: {$searchLimit['remaining']}\n";
            echo "  - Reset: " . date('Y-m-d H:i:s', $searchLimit['reset']) . "\n";
            
            $minutesUntilReset = max(0, round(($searchLimit['reset'] - time()) / 60));
            echo "  - Minutes until reset: {$minutesUntilReset}\n";
        }
        
        // Check v2 endpoint (if available)
        echo "\nNote: Twitter API v2 uses different rate limits\n";
        echo "- Basic tier: 100 requests per 15-minute window\n";
        echo "- Free tier: Lower limits may apply\n";
        
    } else {
        echo "Failed to get rate limit status: HTTP {$response->status()}\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Error checking rate limit: " . $e->getMessage() . "\n";
}

// Suggest solutions
echo "\n========================\n";
echo "Solutions for Rate Limiting:\n";
echo "1. Wait for the rate limit to reset (usually 15 minutes)\n";
echo "2. Use different API credentials\n";
echo "3. Upgrade to a higher Twitter API tier\n";
echo "4. Implement caching to reduce API calls\n";
echo "5. Space out requests with delays\n";