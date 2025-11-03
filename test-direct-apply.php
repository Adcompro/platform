<?php
// Direct test for apply to invoice
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Project;
use App\Models\User;

// Login as admin
$user = User::where('role', 'super_admin')->first();
if (!$user) {
    $user = User::first();
}
Auth::login($user);

echo "Testing Direct Apply to Invoice\n";
echo "================================\n\n";

// Get a project
$project = Project::first();
if (!$project) {
    die("No projects found!\n");
}

echo "Using project: " . $project->name . " (ID: " . $project->id . ")\n\n";

// Check how many invoices exist before
$invoiceCountBefore = \App\Models\Invoice::count();
echo "Invoices before: " . $invoiceCountBefore . "\n\n";

// Create simple test data
$data = [
    'period_start' => '2025-09-01',
    'period_end' => '2025-09-30',
    'consolidated_description' => 'TEST - Direct apply invoice test',
    'line_items' => [
        [
            'description' => 'Test Line 1',
            'quantity' => 5,
            'unit' => 'hours',
            'unit_price' => 100
        ]
    ]
];

// Call the controller directly
try {
    $controller = app(\App\Http\Controllers\InvoiceAITestController::class);
    $request = \Illuminate\Http\Request::create(
        '/invoices/ai-test/' . $project->id . '/apply',
        'POST',
        $data
    );
    
    $result = $controller->applyToInvoice($request, $project);
    
    if ($result instanceof \Illuminate\Http\RedirectResponse) {
        echo "✅ Controller returned redirect response\n";
        
        // Check if invoice was created
        $invoiceCountAfter = \App\Models\Invoice::count();
        echo "Invoices after: " . $invoiceCountAfter . "\n";
        
        if ($invoiceCountAfter > $invoiceCountBefore) {
            echo "✅ Invoice created successfully!\n";
            
            // Get the latest invoice
            $invoice = \App\Models\Invoice::latest()->first();
            echo "\nCreated Invoice:\n";
            echo "  ID: " . $invoice->id . "\n";
            echo "  Draft Name: " . $invoice->draft_name . "\n";
            echo "  Status: " . $invoice->status . "\n";
            echo "  Lines: " . $invoice->lines()->count() . "\n";
        } else {
            echo "❌ No invoice was created\n";
        }
    } else {
        echo "❌ Unexpected response type\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}

echo "\nDone!\n";