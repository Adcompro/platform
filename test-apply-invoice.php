<?php
// Test the applyToInvoice method
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Project;
use App\Models\User;
use App\Http\Controllers\InvoiceAITestController;
use Illuminate\Http\Request;

// Login as admin
$user = User::where('role', 'super_admin')->first();
if (!$user) {
    $user = User::first();
}
Auth::login($user);

echo "Testing Apply to Invoice\n";
echo "=========================\n\n";

// Get a project
$project = Project::first();
if (!$project) {
    die("No projects found!\n");
}

echo "Using project: " . $project->name . " (ID: " . $project->id . ")\n\n";

try {
    // Create a mock request with the data that would come from the form
    $requestData = [
        'period_start' => '2025-08-01',
        'period_end' => '2025-08-31',
        'consolidated_description' => 'AI Generated - Development and consulting services for August 2025',
        'line_items' => [
            [
                'description' => 'Development work - Feature implementation and bug fixes',
                'quantity' => 20,
                'unit' => 'hours',
                'unit_price' => 75
            ],
            [
                'description' => 'Consulting - Project planning and architecture review',
                'quantity' => 10,
                'unit' => 'hours',
                'unit_price' => 100
            ]
        ]
    ];
    
    $request = Request::create(
        '/invoices/ai-test/' . $project->id . '/apply',
        'POST',
        $requestData
    );
    
    // Inject the request
    app()->instance('request', $request);
    
    // Call the controller method
    $aiService = app(\App\Services\ClaudeAIService::class);
    $controller = new InvoiceAITestController($aiService);
    
    $result = $controller->applyToInvoice($request, $project);
    
    if ($result instanceof \Illuminate\Http\RedirectResponse) {
        $targetUrl = $result->getTargetUrl();
        echo "✅ Success! Response is a redirect\n";
        echo "   Target URL: " . $targetUrl . "\n";
        
        // Check session for success message
        $session = $result->getSession();
        if ($session && $session->has('success')) {
            echo "   Success message: " . $session->get('success') . "\n";
        }
        
        // Try to find the most recent invoice for this project
        $latestInvoice = \App\Models\Invoice::where('project_id', $project->id)
            ->where('ai_generated', true)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($latestInvoice) {
            echo "\n✅ Found latest AI invoice for this project:\n";
            $invoiceId = $latestInvoice->id;
            echo "   Invoice ID: " . $invoiceId . "\n";
            
            // Verify invoice exists and show details
            $invoice = \App\Models\Invoice::with('lines')->find($invoiceId);
            if ($invoice) {
                echo "✅ Invoice verified in database!\n";
                echo "   - Draft Name: " . $invoice->draft_name . "\n";
                echo "   - Status: " . $invoice->status . "\n";
                echo "   - AI Generated: " . ($invoice->ai_generated ? 'Yes' : 'No') . "\n";
                echo "   - Period: " . $invoice->period_start . " to " . $invoice->period_end . "\n";
                echo "   - Lines count: " . $invoice->lines->count() . "\n\n";
                
                if ($invoice->lines->count() > 0) {
                    echo "Invoice Lines:\n";
                    foreach ($invoice->lines as $line) {
                        echo "   - " . $line->description . " (" . $line->quantity . " " . $line->unit . " @ €" . $line->unit_price . ")\n";
                    }
                }
            }
        }
    } else {
        echo "❌ Unexpected response type: " . get_class($result) . "\n";
        if (method_exists($result, 'getContent')) {
            echo "Content: " . substr($result->getContent(), 0, 500) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nDone!\n";