<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecurringProjectService;
use Illuminate\Support\Facades\Log;

class ProcessRecurringProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:process-recurring
                            {--force : Force processing even if not within threshold}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring projects and create new projects for upcoming periods';

    /**
     * Execute the console command.
     */
    public function handle(RecurringProjectService $service): int
    {
        $this->info('Starting recurring projects processing...');
        Log::info('Recurring projects command started');

        try {
            $results = $service->processRecurringProjects();

            // Display results
            $this->info('Processing completed:');
            $this->line("  Processed: {$results['processed']} projects");
            $this->line("  Created: {$results['created']} new projects");
            $this->line("  Skipped: {$results['skipped']} projects");
            $this->line("  Errors: {$results['errors']} errors");

            if (!empty($results['details'])) {
                $this->newLine();
                $this->info('Details:');
                foreach ($results['details'] as $detail) {
                    if ($detail['action'] === 'created') {
                        $this->line("  ✓ Created: {$detail['new_project']} (period: {$detail['period']})");
                    } elseif ($detail['action'] === 'error') {
                        $this->error("  ✗ Error in {$detail['project']}: {$detail['error']}");
                    }
                }
            }

            Log::info('Recurring projects command completed', $results);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Fatal error processing recurring projects: ' . $e->getMessage());
            Log::error('Recurring projects command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}
