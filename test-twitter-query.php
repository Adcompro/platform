<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserMediaMonitor;

echo "Twitter Search Query Builder Test\n";
echo "==================================\n\n";

// Simuleer verschillende zoektermen
$testCases = [
    // Korte keywords
    ['Boeing', 'Dreamliner'],
    
    // Langere omschrijving
    ['artificial intelligence', 'machine learning', 'deep learning', 'neural networks'],
    
    // Mix van kort en lang
    ['iPhone', 'Apple smartphone', 'iOS 18 features', 'mobile technology'],
    
    // Nederlandse termen
    ['robotstofzuiger', 'stofzuigrobot voor trappen', 'automatisch stofzuigen']
];

echo "Hoe Twitter queries worden gebouwd:\n";
echo "------------------------------------\n\n";

foreach ($testCases as $index => $keywords) {
    echo "Test " . ($index + 1) . ": " . implode(', ', $keywords) . "\n";
    
    // Build query zoals in TwitterService
    $query = implode(' OR ', array_map(fn($k) => '"' . $k . '"', $keywords));
    $query .= ' (lang:nl OR lang:en OR lang:fr)';
    
    echo "Twitter Query: " . $query . "\n";
    echo "Query Length: " . strlen($query) . " characters\n\n";
}

echo "Actuele monitors in database:\n";
echo "------------------------------\n";
$monitors = UserMediaMonitor::where('is_active', true)
    ->whereNotNull('keywords')
    ->get();

foreach ($monitors as $monitor) {
    echo "\nMonitor: {$monitor->name}\n";
    $keywords = is_string($monitor->keywords) 
        ? json_decode($monitor->keywords, true) 
        : $monitor->keywords;
    
    if (is_array($keywords)) {
        echo "Keywords: " . implode(', ', $keywords) . "\n";
        
        // Build actual query
        $query = implode(' OR ', array_map(fn($k) => '"' . $k . '"', $keywords));
        $query .= ' (lang:nl OR lang:en OR lang:fr)';
        
        echo "Twitter Query: " . $query . "\n";
        echo "Query Length: " . strlen($query) . " characters\n";
    }
}

echo "\n==================================\n";
echo "Twitter API Query Limits:\n";
echo "- Max query length: 512 characters\n";
echo "- Max operators: Onbeperkt OR statements\n";
echo "- Ondersteunt: exact match (\"term\"), OR, AND, NOT, lang:\n";
echo "- Free tier: 1 request per 15 minuten\n";
echo "- Basic tier: 100 requests per 15 minuten\n";