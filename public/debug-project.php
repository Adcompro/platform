<?php
// Debug project state
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

Auth::loginUsingId(2);

$projectId = $_GET['id'] ?? 3;
$project = \App\Models\Project::find($projectId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'clear_cache') {
        // Clear all caches
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        echo "<p style='color: green;'>All caches cleared!</p>";
    } elseif ($_POST['action'] === 'refresh_model') {
        // Force refresh from database
        $project->refresh();
        $project->load('users');
        echo "<p style='color: green;'>Model refreshed from database!</p>";
    }
}

// Always reload to get fresh data
$project->load('users');

// Get raw database data
$rawTeamMembers = DB::table('project_users')
    ->where('project_id', $projectId)
    ->join('users', 'project_users.user_id', '=', 'users.id')
    ->select('users.id', 'users.name', 'users.email')
    ->get();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Project <?= $projectId ?></title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        .button:hover { background: #0056b3; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #e3f2fd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Debug Project #<?= $projectId ?>: <?= $project->name ?></h1>
    
    <div class="card">
        <h2>Quick Actions</h2>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="button">Clear All Caches</button>
        </form>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="refresh_model">
            <button type="submit" class="button">Refresh Model</button>
        </form>
        <a href="/projects/<?= $projectId ?>/edit-tabbed" class="button" style="text-decoration: none; display: inline-block;">
            Edit Project (Tabbed)
        </a>
    </div>

    <div class="card">
        <h2>Eloquent Model State</h2>
        <div class="info">
            <strong>Model Class:</strong> <?= get_class($project) ?><br>
            <strong>Loaded Relations:</strong> <?= implode(', ', array_keys($project->getRelations())) ?><br>
            <strong>Team Members Count:</strong> <?= $project->users->count() ?><br>
            <strong>Last Updated:</strong> <?= $project->updated_at ?>
        </div>
        
        <h3>Team Members from Eloquent Relationship</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Pivot Exists</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($project->users as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td><?= $user->name ?></td>
                    <td><?= $user->email ?></td>
                    <td><?= $user->pivot ? 'Yes' : 'No' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Raw Database State</h2>
        <div class="info">
            <strong>Direct Query Count:</strong> <?= $rawTeamMembers->count() ?><br>
            <strong>Table:</strong> project_users<br>
            <strong>Query:</strong> <code>SELECT * FROM project_users WHERE project_id = <?= $projectId ?></code>
        </div>
        
        <h3>Team Members from Direct Database Query</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rawTeamMembers as $member): ?>
                <tr>
                    <td><?= $member->id ?></td>
                    <td><?= $member->name ?></td>
                    <td><?= $member->email ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Comparison</h2>
        <?php
        $eloquentIds = $project->users->pluck('id')->toArray();
        $rawIds = $rawTeamMembers->pluck('id')->toArray();
        $match = $eloquentIds === $rawIds;
        ?>
        <div class="<?= $match ? 'success' : 'error' ?>">
            <?php if ($match): ?>
                ✓ Eloquent and Database are in sync
            <?php else: ?>
                ✗ Mismatch between Eloquent and Database!
                <br>Eloquent IDs: <?= implode(', ', $eloquentIds) ?>
                <br>Database IDs: <?= implode(', ', $rawIds) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Test Form Submission</h2>
        <p>Test if form data is being sent correctly:</p>
        <form action="/projects/<?= $projectId ?>" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_tabbed_editor" value="1">
            <input type="hidden" name="name" value="<?= $project->name ?>">
            <input type="hidden" name="customer_id" value="<?= $project->customer_id ?>">
            <input type="hidden" name="status" value="<?= $project->status ?>">
            <input type="hidden" name="start_date" value="<?= $project->start_date->format('Y-m-d') ?>">
            <input type="hidden" name="billing_frequency" value="<?= $project->billing_frequency ?>">
            
            <h4>Select Team Members:</h4>
            <?php foreach (\App\Models\User::all() as $user): ?>
            <label style="display: block; margin: 5px 0;">
                <input type="checkbox" name="team_members[]" value="<?= $user->id ?>"
                       <?= $project->users->contains($user->id) ? 'checked' : '' ?>>
                <?= $user->name ?> (ID: <?= $user->id ?>)
            </label>
            <?php endforeach; ?>
            
            <button type="submit" class="button" style="margin-top: 10px;">
                Test Update Project
            </button>
        </form>
    </div>
</body>
</html>