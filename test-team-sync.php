<?php
// Test script to debug team member sync issue

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

$projectId = 3; // Test project ID

echo "=== TEAM SYNC TEST ===\n";

// Step 1: Check current team members
$currentMembers = DB::table('project_users')
    ->where('project_id', $projectId)
    ->pluck('user_id')
    ->toArray();

echo "Current team members in DB: " . json_encode($currentMembers) . "\n";

// Step 2: Load project with Eloquent
$project = Project::find($projectId);
$eloquentMembers = $project->users->pluck('id')->toArray();

echo "Team members via Eloquent: " . json_encode($eloquentMembers) . "\n";

// Step 3: Try to sync empty array
echo "\nAttempting to sync empty array...\n";

DB::beginTransaction();

try {
    $result = $project->users()->sync([]);
    echo "Sync result: " . json_encode($result) . "\n";
    
    // Check immediately after sync (before commit)
    $afterSyncBeforeCommit = DB::table('project_users')
        ->where('project_id', $projectId)
        ->pluck('user_id')
        ->toArray();
    
    echo "After sync (before commit): " . json_encode($afterSyncBeforeCommit) . "\n";
    
    DB::commit();
    echo "Transaction committed\n";
    
    // Check after commit
    $afterCommit = DB::table('project_users')
        ->where('project_id', $projectId)
        ->pluck('user_id')
        ->toArray();
    
    echo "After commit: " . json_encode($afterCommit) . "\n";
    
    // Reload project and check
    $project->unsetRelation('users');
    $project->load('users');
    $reloadedMembers = $project->users->pluck('id')->toArray();
    
    echo "After reload: " . json_encode($reloadedMembers) . "\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
}

// Step 4: Direct SQL delete test
echo "\n=== DIRECT SQL DELETE TEST ===\n";

$deleted = DB::table('project_users')
    ->where('project_id', $projectId)
    ->delete();

echo "Deleted $deleted rows directly\n";

$afterDelete = DB::table('project_users')
    ->where('project_id', $projectId)
    ->pluck('user_id')
    ->toArray();

echo "After direct delete: " . json_encode($afterDelete) . "\n";

// Step 5: Check if there are any constraints or triggers
echo "\n=== DATABASE CONSTRAINTS CHECK ===\n";

$constraints = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        CONSTRAINT_TYPE,
        TABLE_NAME,
        REFERENCED_TABLE_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_NAME = 'project_users'
        AND TABLE_SCHEMA = DATABASE()
");

foreach ($constraints as $constraint) {
    echo "Constraint: " . json_encode($constraint) . "\n";
}