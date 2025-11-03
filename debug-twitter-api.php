<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

echo "Twitter API Debug Test\n";
echo "======================\n\n";

// Get Twitter source
$source = SocialMediaSource::where('platform', 'twitter')->first();

if (!$source) {
    echo "No Twitter source found\n";
    exit(1);
}

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

echo "Step 1: Check rate limit status\n";
echo "--------------------------------\n";
if ($source->isRateLimited()) {
    echo "Currently rate limited until: " . $source->rate_limit_reset_at . "\n";
    $wait = Carbon::now()->diffInSeconds($source->rate_limit_reset_at);
    if ($wait > 0) {
        echo "Waiting {$wait} seconds...\n";
        sleep($wait + 5); // Wait extra 5 seconds to be safe
    }
} else {
    echo "Not rate limited\n";
}

echo "\nStep 2: Test with simplest possible query\n";
echo "------------------------------------------\n";
// Use very common word to ensure results
$testQuery = 'the';
echo "Query: '{$testQuery}'\n\n";

try {
    // Try v2 API with minimal parameters
    $response = Http::withToken($bearerToken)
        ->timeout(30)
        ->get('https://api.twitter.com/2/tweets/search/recent', [
            'query' => $testQuery,
            'max_results' => 10
        ]);
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Headers:\n";
    $headers = $response->headers();
    foreach (['x-rate-limit-limit', 'x-rate-limit-remaining', 'x-rate-limit-reset'] as $header) {
        if (isset($headers[$header])) {
            $value = is_array($headers[$header]) ? $headers[$header][0] : $headers[$header];
            echo "  {$header}: {$value}\n";
        }
    }
    
    echo "\nResponse Body:\n";
    $body = $response->json();
    
    if ($response->successful()) {
        if (isset($body['data']) && !empty($body['data'])) {
            echo "SUCCESS! Found " . count($body['data']) . " tweets\n";
            echo "First tweet ID: " . $body['data'][0]['id'] . "\n";
            echo "First tweet text: " . substr($body['data'][0]['text'], 0, 100) . "...\n";
        } else {
            echo "Response successful but no data returned\n";
            echo "Full response: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "Request failed!\n";
        echo "Error response: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";
        
        if ($response->status() === 401) {
            echo "\n⚠️ Authentication failed - Bearer token may be invalid or expired\n";
        } elseif ($response->status() === 403) {
            echo "\n⚠️ Forbidden - Check if your app has the right permissions\n";
        } elseif ($response->status() === 429) {
            echo "\n⚠️ Rate limited - Try again later\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nStep 3: Check Twitter API access level\n";
echo "---------------------------------------\n";
echo "Based on the rate limits:\n";
if (isset($headers['x-rate-limit-limit'])) {
    $limit = is_array($headers['x-rate-limit-limit']) ? $headers['x-rate-limit-limit'][0] : $headers['x-rate-limit-limit'];
    if ($limit == 1) {
        echo "❌ You have FREE tier access (1 request per 15 min)\n";
        echo "This is essentially unusable for production\n";
    } elseif ($limit <= 100) {
        echo "✓ You have BASIC tier access ({$limit} requests per 15 min)\n";
    } else {
        echo "✓ You have PRO or higher tier access ({$limit} requests per 15 min)\n";
    }
}

echo "\nStep 4: Test if v1.1 API works\n";
echo "--------------------------------\n";
try {
    $v1Response = Http::withToken($bearerToken)
        ->timeout(30)
        ->get('https://api.twitter.com/1.1/search/tweets.json', [
            'q' => $testQuery,
            'count' => 5
        ]);
    
    echo "v1.1 API Status: " . $v1Response->status() . "\n";
    if ($v1Response->successful()) {
        $v1Data = $v1Response->json();
        if (isset($v1Data['statuses'])) {
            echo "v1.1 API works! Found " . count($v1Data['statuses']) . " tweets\n";
        }
    } else {
        echo "v1.1 API failed with status " . $v1Response->status() . "\n";
    }
} catch (\Exception $e) {
    echo "v1.1 API error: " . $e->getMessage() . "\n";
}