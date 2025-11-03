<?php
// Manual cleanup script for team members

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$projectId = 3; // Test project

echo "=== MANUAL TEAM CLEANUP ===\n";

// First check what's there
$current = DB::table('project_users')
    ->where('project_id', $projectId)
    ->get();

echo "Current records:\n";
foreach ($current as $record) {
    echo json_encode($record) . "\n";
}

// Delete all
$deleted = DB::table('project_users')
    ->where('project_id', $projectId)
    ->delete();

echo "\nDeleted $deleted records\n";

// Verify
$after = DB::table('project_users')
    ->where('project_id', $projectId)
    ->count();

echo "Records after delete: $after\n";

// Now check if they come back on their own
echo "\nWaiting 2 seconds...\n";
sleep(2);

$afterWait = DB::table('project_users')
    ->where('project_id', $projectId)
    ->count();

echo "Records after waiting: $afterWait\n";

if ($afterWait > 0) {
    echo "WARNING: Records came back on their own!\n";
    $mysterRecords = DB::table('project_users')
        ->where('project_id', $projectId)
        ->get();
    foreach ($mysterRecords as $record) {
        echo json_encode($record) . "\n";
    }
}