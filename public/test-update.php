<?php
// Direct test of project update
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Force authentication
Auth::loginUsingId(2); // Login as user 2

$project = \App\Models\Project::find(3);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $teamMembers = $_POST['team_members'] ?? [];
        echo "<h3>Updating team members to: " . implode(', ', $teamMembers) . "</h3>";
        
        // Direct database update
        DB::beginTransaction();
        try {
            // Clear existing
            DB::table('project_users')->where('project_id', 3)->delete();
            
            // Add new
            foreach ($teamMembers as $userId) {
                DB::table('project_users')->insert([
                    'project_id' => 3,
                    'user_id' => $userId,
                    'can_edit_fee' => false,
                    'can_view_financials' => false,
                    'can_log_time' => true,
                    'can_approve_time' => false,
                    'added_by' => 2,
                    'added_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            DB::commit();
            echo "<p style='color: green;'>Team members updated successfully!</p>";
        } catch (Exception $e) {
            DB::rollback();
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Reload project
    $project = \App\Models\Project::find(3);
}

$users = \App\Models\User::all();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Update Test</title>
</head>
<body>
    <h1>Direct Update Test for Project 3</h1>
    
    <h2>Current Team Members:</h2>
    <ul>
        <?php foreach ($project->users as $member): ?>
            <li><?= $member->name ?> (ID: <?= $member->id ?>)</li>
        <?php endforeach; ?>
    </ul>
    
    <h2>Update Team Members:</h2>
    <form method="POST">
        <input type="hidden" name="action" value="update">
        <?php foreach ($users as $user): ?>
            <label style="display: block; margin: 5px 0;">
                <input type="checkbox" name="team_members[]" value="<?= $user->id ?>" 
                       <?= $project->users->contains($user->id) ? 'checked' : '' ?>>
                <?= $user->name ?> (ID: <?= $user->id ?>)
            </label>
        <?php endforeach; ?>
        <button type="submit" style="margin-top: 10px; padding: 10px 20px; background: blue; color: white;">
            UPDATE TEAM MEMBERS
        </button>
    </form>
</body>
</html>