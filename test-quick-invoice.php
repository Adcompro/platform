<?php
// Direct test for quickCreate method
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Project;
use App\Models\User;
use App\Http\Controllers\InvoiceAITestController;

// Login as admin
$user = User::where('role', 'super_admin')->first();
if (!$user) {
    $user = User::first();
}
Auth::login($user);

echo "Testing Quick Invoice Creation\n";
echo "===============================\n\n";

// Get a project
$project = Project::first();
if (!$project) {
    die("No projects found!\n");
}

echo "Using project: " . $project->name . " (ID: " . $project->id . ")\n";

try {
    // Call the controller method directly with dependency injection
    $aiService = app(\App\Services\ClaudeAIService::class);
    $controller = new InvoiceAITestController($aiService);
    
    // Mock the request
    app()->instance('request', new \Illuminate\Http\Request());
    
    $result = $controller->quickCreate($project);
    
    if ($result instanceof \Illuminate\Http\RedirectResponse) {
        $targetUrl = $result->getTargetUrl();
        echo "✅ Success! Redirect to: " . $targetUrl . "\n";
        
        // Extract invoice ID from URL
        if (preg_match('/invoices\/(\d+)\/edit/', $targetUrl, $matches)) {
            $invoiceId = $matches[1];
            echo "✅ Invoice created with ID: " . $invoiceId . "\n";
            
            // Verify invoice exists
            $invoice = \App\Models\Invoice::find($invoiceId);
            if ($invoice) {
                echo "✅ Invoice verified in database!\n";
                echo "   - Draft Name: " . $invoice->draft_name . "\n";
                echo "   - Status: " . $invoice->status . "\n";
                echo "   - AI Generated: " . ($invoice->ai_generated ? 'Yes' : 'No') . "\n";
            }
        }
    } else {
        echo "❌ Unexpected response type: " . get_class($result) . "\n";
        if (method_exists($result, 'getContent')) {
            echo "Content: " . $result->getContent() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nDone!\n";