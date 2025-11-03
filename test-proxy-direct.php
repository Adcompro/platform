<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserMediaMention;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "========================================\n";
echo "Direct Proxy Test\n";
echo "========================================\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Login as first user for testing
$user = User::first();
Auth::login($user);

$mention = UserMediaMention::first();
if (!$mention) {
    echo "No articles found!\n";
    exit;
}

echo "Testing article fetch for:\n";
echo "• Title: {$mention->article_title}\n";
echo "• URL: {$mention->article_url}\n\n";

try {
    // Test the proxy functionality directly
    $client = new \GuzzleHttp\Client([
        'timeout' => 10,
        'verify' => false,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    echo "Fetching article content...\n";
    $response = $client->get($mention->article_url);
    $statusCode = $response->getStatusCode();
    $contentType = $response->getHeader('Content-Type')[0] ?? 'unknown';
    $bodySize = strlen((string) $response->getBody());
    
    echo "✅ SUCCESS!\n";
    echo "• Status Code: {$statusCode}\n";
    echo "• Content Type: {$contentType}\n";
    echo "• Content Size: " . number_format($bodySize) . " bytes\n";
    echo "• Article can be displayed in modal\n";
    
} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "❌ REQUEST ERROR:\n";
    echo "• " . $e->getMessage() . "\n";
    
    if ($e->hasResponse()) {
        $statusCode = $e->getResponse()->getStatusCode();
        echo "• Status Code: {$statusCode}\n";
    }
    
    echo "\n⚠️ This article will show fallback page\n";
    echo "• Users can still open in new tab\n";
    
} catch (\Exception $e) {
    echo "❌ GENERAL ERROR:\n";
    echo "• " . $e->getMessage() . "\n";
    echo "\n⚠️ Fallback page will be shown\n";
}

echo "\n========================================\n";
echo "Proxy test complete!\n";
echo "========================================\n";