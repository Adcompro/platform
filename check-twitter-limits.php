<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "Twitter API Usage Status\n";
echo "========================\n\n";

echo "Current Issue:\n";
echo "--------------\n";
echo "❌ Monthly usage cap exceeded for 'standard-basic' tier\n";
echo "   Account ID: 1964048466910625792\n\n";

echo "What this means:\n";
echo "----------------\n";
echo "• You have Twitter Basic API access ($100/month)\n";
echo "• Basic tier includes a monthly tweet cap\n";
echo "• You've hit your monthly limit for September 2025\n";
echo "• The limit will reset on October 1st, 2025\n\n";

echo "Twitter Basic Tier Limits:\n";
echo "--------------------------\n";
echo "• Tweet reads: 10,000 tweets per month\n";
echo "• Rate limit: 100 requests per 15 minutes\n";
echo "• But once monthly cap is hit, ALL requests blocked\n\n";

$today = Carbon::now();
$nextMonth = Carbon::now()->startOfMonth()->addMonth();
$daysLeft = $today->diffInDays($nextMonth);

echo "Reset Information:\n";
echo "------------------\n";
echo "• Current date: " . $today->format('Y-m-d H:i:s') . "\n";
echo "• Reset date: " . $nextMonth->format('Y-m-d') . " 00:00:00 UTC\n";
echo "• Days until reset: {$daysLeft} days\n\n";

echo "Solutions:\n";
echo "----------\n";
echo "1. Wait until " . $nextMonth->format('F 1, Y') . " for automatic reset\n";
echo "2. Upgrade to Twitter Pro tier ($5000/month) for higher limits\n";
echo "3. Purchase additional tweet reads as add-on\n";
echo "4. Contact Twitter support to check exact usage\n\n";

echo "Alternative Options:\n";
echo "--------------------\n";
echo "• Use LinkedIn API for social monitoring (if configured)\n";
echo "• Focus on RSS feeds for content monitoring\n";
echo "• Implement webhooks instead of polling\n";
echo "• Use Twitter's free streaming API (if available)\n";