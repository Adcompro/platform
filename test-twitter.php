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

echo "Twitter Source Found:\n";
echo "- Name: {$source->name}\n";
echo "- Is Active: " . ($source->is_active ? 'Yes' : 'No') . "\n";
echo "- Account Name: {$source->account_name}\n";

// Check credentials
if ($source->api_credentials) {
    echo "- Has Credentials: Yes\n";
    $credKeys = array_keys($source->api_credentials);
    echo "- Credential Keys: " . implode(', ', $credKeys) . "\n";
    
    // Check if credentials are encrypted
    foreach ($credKeys as $key) {
        $value = $source->api_credentials[$key];
        $isEncrypted = is_string($value) && str_starts_with($value, 'eyJ');
        echo "  - {$key}: " . ($isEncrypted ? 'Encrypted' : 'Plain text') . "\n";
    }
} else {
    echo "- Has Credentials: No\n";
}

// Test the Twitter service
echo "\nTesting Twitter Service:\n";
$service = new TwitterService($source);

if ($service->isConfigured()) {
    echo "- Service is configured\n";
    
    // Test connection
    echo "- Testing connection...\n";
    $connected = $service->testConnection();
    echo "- Connection test: " . ($connected ? 'SUCCESS' : 'FAILED') . "\n";
    
    if (!$connected) {
        echo "- Check your Twitter API credentials\n";
        echo "- Make sure you have a valid Bearer Token or API Keys\n";
    }
} else {
    echo "- Service is NOT configured\n";
    echo "- Missing required credentials (Bearer Token)\n";
}

// Check for monitors
echo "\nChecking for User Media Monitors:\n";
$monitors = \App\Models\UserMediaMonitor::where('is_active', true)->get();
echo "- Active monitors: " . $monitors->count() . "\n";

foreach ($monitors as $monitor) {
    echo "  - Monitor #{$monitor->id}: {$monitor->name}\n";
    if ($monitor->keywords) {
        echo "    Keywords: " . implode(', ', $monitor->keywords) . "\n";
    }
    if ($monitor->accounts) {
        echo "    Accounts: " . implode(', ', $monitor->accounts) . "\n";
    }
}