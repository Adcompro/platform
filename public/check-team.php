<?php
// Direct database check for team members
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$projectId = $_GET['id'] ?? 3;

// Direct database query
$teamMembers = DB::table('project_users')
    ->where('project_id', $projectId)
    ->join('users', 'project_users.user_id', '=', 'users.id')
    ->select('project_users.*', 'users.name', 'users.email')
    ->get();

// Using Eloquent
$project = \App\Models\Project::find($projectId);
$eloquentMembers = $project ? $project->users : collect();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Team Members Check - Project <?= $projectId ?></title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .section { margin: 30px 0; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Team Members Check - Project <?= $projectId ?></h1>
    
    <div class="section">
        <h2>Direct Database Query (project_users table)</h2>
        <p>Total members: <?= count($teamMembers) ?></p>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Can Edit Fee</th>
                    <th>Can View Financials</th>
                    <th>Can Log Time</th>
                    <th>Can Approve Time</th>
                    <th>Added By</th>
                    <th>Added At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teamMembers as $member): ?>
                <tr>
                    <td><?= $member->user_id ?></td>
                    <td><?= $member->name ?></td>
                    <td><?= $member->email ?></td>
                    <td><?= $member->can_edit_fee ? 'Yes' : 'No' ?></td>
                    <td><?= $member->can_view_financials ? 'Yes' : 'No' ?></td>
                    <td><?= $member->can_log_time ? 'Yes' : 'No' ?></td>
                    <td><?= $member->can_approve_time ? 'Yes' : 'No' ?></td>
                    <td><?= $member->added_by ?></td>
                    <td><?= $member->added_at ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>Eloquent Relationship (project->users)</h2>
        <?php if ($project): ?>
            <p>Total members: <?= count($eloquentMembers) ?></p>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Pivot Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eloquentMembers as $user): ?>
                    <tr>
                        <td><?= $user->id ?></td>
                        <td><?= $user->name ?></td>
                        <td><?= $user->email ?></td>
                        <td>
                            <?php if ($user->pivot): ?>
                                Can Edit Fee: <?= $user->pivot->can_edit_fee ? 'Yes' : 'No' ?><br>
                                Can View Financials: <?= $user->pivot->can_view_financials ? 'Yes' : 'No' ?><br>
                                Can Log Time: <?= $user->pivot->can_log_time ? 'Yes' : 'No' ?><br>
                                Can Approve Time: <?= $user->pivot->can_approve_time ? 'Yes' : 'No' ?>
                            <?php else: ?>
                                No pivot data
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="error">Project not found!</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Test Links</h2>
        <p>
            <a href="/projects/<?= $projectId ?>/edit-tabbed">Edit Project (Tabbed)</a> | 
            <a href="/projects/<?= $projectId ?>">View Project</a> |
            <a href="?id=<?= $projectId ?>&refresh=<?= time() ?>">Refresh (No Cache)</a>
        </p>
    </div>
</body>
</html>