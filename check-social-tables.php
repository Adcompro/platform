<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Social Media Database Schema Check\n";
echo "===================================\n\n";

// Check if tables exist
$tables = [
    'social_media_sources',
    'social_media_mentions',
    'user_media_monitors'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "✓ Table '{$table}' exists\n";
        
        // Get columns
        $columns = Schema::getColumnListing($table);
        echo "  Columns: " . count($columns) . "\n";
        
        // Show columns using raw query for column info
        $columnInfo = DB::select("SHOW COLUMNS FROM {$table}");
        foreach ($columnInfo as $col) {
            $nullable = $col->Null === 'YES' ? 'nullable' : 'required';
            echo "    - {$col->Field} ({$col->Type}, {$nullable})\n";
        }
        echo "\n";
    } else {
        echo "✗ Table '{$table}' DOES NOT exist\n\n";
    }
}

// Check specific required fields for social_media_mentions
echo "Checking Required Fields for social_media_mentions:\n";
echo "---------------------------------------------------\n";

$requiredFields = [
    'id',
    'source_id',
    'platform_post_id',
    'author_name',
    'author_handle',
    'author_profile_url',
    'author_followers',
    'author_verified',
    'content',
    'hashtags',
    'mentions',
    'urls',
    'media_urls',
    'post_url',
    'published_at',
    'likes_count',
    'shares_count',
    'comments_count',
    'views_count',
    'engagement_rate',
    'post_type',
    'in_reply_to',
    'is_repost',
    'raw_data',
    'created_at',
    'updated_at'
];

if (Schema::hasTable('social_media_mentions')) {
    $existingColumns = Schema::getColumnListing('social_media_mentions');
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $existingColumns)) {
            echo "✓ {$field}\n";
        } else {
            echo "✗ {$field} - MISSING!\n";
        }
    }
} else {
    echo "Cannot check fields - table does not exist!\n";
}

echo "\n";

// Check for any sample data
echo "Sample Data Check:\n";
echo "------------------\n";

if (Schema::hasTable('social_media_mentions')) {
    $count = DB::table('social_media_mentions')->count();
    echo "Total mentions in database: {$count}\n";
    
    if ($count > 0) {
        $latest = DB::table('social_media_mentions')
            ->orderBy('created_at', 'desc')
            ->first();
        echo "Latest mention created: " . $latest->created_at . "\n";
        echo "Platform: " . ($latest->platform_post_id ?? 'N/A') . "\n";
    }
}

if (Schema::hasTable('user_media_monitors')) {
    $monitorCount = DB::table('user_media_monitors')->where('is_active', true)->count();
    echo "Active monitors: {$monitorCount}\n";
}