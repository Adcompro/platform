<?php
// Test what data is being posted

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

// Create a fake request to test
$request = Request::create('/projects/1', 'PUT', [
    '_token' => 'test',
    '_method' => 'PUT',
    'from_tabbed_editor' => '1',
    'name' => 'Test Project',
    'customer_id' => '1',
    'status' => 'active',
    'start_date' => '2025-09-01',
    'monthly_fee' => '1000',
    'main_invoicing_company_id' => '1',
    'billing_frequency' => 'monthly',
    'team_members' => [2, 3]
]);

echo "=== TEST REQUEST DATA ===\n";
echo "Has team_members: " . ($request->has('team_members') ? 'YES' : 'NO') . "\n";
echo "Team members value: " . json_encode($request->input('team_members')) . "\n";
echo "All input: " . json_encode($request->all()) . "\n";

// Test validation
$rules = [
    'team_members' => 'nullable|array',
    'team_members.*' => 'exists:users,id'
];

$validator = \Validator::make($request->all(), $rules);

if ($validator->fails()) {
    echo "\nValidation FAILED:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "- $error\n";
    }
} else {
    echo "\nValidation PASSED\n";
    $validated = $validator->validated();
    echo "Validated team_members: " . json_encode($validated['team_members'] ?? []) . "\n";
}