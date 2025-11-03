<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeamleaderService;
use App\Models\TeamleaderTimeEntry;
use App\Models\TeamleaderProject;
use App\Models\TeamleaderTask;
use App\Models\Customer;
use Carbon\Carbon;

class FetchTargetedTimeEntries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'teamleader:fetch-targeted
                            {--customer= : Customer ID or name to filter}
                            {--project= : Project ID or name to filter}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--months=12 : Number of months back (if from/to not specified)}
                            {--export : Export results to CSV}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch time entries for specific customer/project/date range (targeted approach)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 TARGETED TIME ENTRY FETCHER');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Step 1: Select Customer
        $customer = $this->selectCustomer();
        if (!$customer) {
            $this->error('No customer selected. Aborting.');
            return 1;
        }

        // Step 2: Select Project(s)
        $projects = $this->selectProjects($customer);
        if ($projects->isEmpty()) {
            $this->error('No projects found for this customer. Aborting.');
            return 1;
        }

        // Step 3: Determine Date Range
        $dateRange = $this->determineDateRange();

        // Step 4: Find Time Entries
        $entries = $this->findTimeEntries($customer, $projects, $dateRange);

        // Step 5: Display Results
        $this->displayResults($customer, $projects, $dateRange, $entries);

        // Step 6: Export if requested
        if ($this->option('export')) {
            $this->exportResults($customer, $entries);
        }

        return 0;
    }

    protected function selectCustomer()
    {
        $customerOption = $this->option('customer');

        if ($customerOption) {
            // Try to find by ID first
            if (is_numeric($customerOption)) {
                $customer = Customer::find($customerOption);
            } else {
                // Try to find by name
                $customer = Customer::where('name', 'like', "%{$customerOption}%")->first();
            }

            if ($customer) {
                $this->info("✓ Selected customer: {$customer->name}");
                return $customer;
            }
        }

        // Interactive selection
        $customers = Customer::whereNotNull('teamleader_id')
            ->orderBy('name')
            ->get();

        if ($customers->isEmpty()) {
            $this->error('No customers found with Teamleader ID.');
            return null;
        }

        $this->info('📋 AVAILABLE CUSTOMERS:');
        $this->table(
            ['#', 'Name', 'Teamleader ID', 'Projects'],
            $customers->map(function($customer, $index) {
                $projectCount = TeamleaderProject::where('customer_id', $customer->id)->count();
                return [
                    $index + 1,
                    $customer->name,
                    substr($customer->teamleader_id, 0, 12) . '...',
                    $projectCount
                ];
            })
        );

        $choice = $this->ask('Select customer number (or 0 for Anker default)');

        if ($choice == '0') {
            $customer = Customer::where('name', 'like', '%Anker%')->first();
            if ($customer) {
                $this->info("✓ Using Anker customer: {$customer->name}");
                return $customer;
            }
        }

        if (isset($customers[$choice - 1])) {
            $customer = $customers[$choice - 1];
            $this->info("✓ Selected: {$customer->name}");
            return $customer;
        }

        return null;
    }

    protected function selectProjects($customer)
    {
        $projectOption = $this->option('project');

        // First try to get from Progress projects (imported), then from cache
        $progressProjects = \App\Models\Project::where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        $cacheProjects = TeamleaderProject::where('customer_id', $customer->id)
            ->orderBy('title')
            ->get();

        // If we have Progress projects, use those (they're imported and active)
        if ($progressProjects->isNotEmpty()) {
            $this->info("Found {$progressProjects->count()} imported Progress projects");

            // Convert to TeamleaderProject-like structure for compatibility
            $projects = $progressProjects->map(function($p) {
                return (object)[
                    'id' => $p->id,
                    'teamleader_id' => $p->teamleader_id,
                    'title' => $p->name,
                    'starts_on' => $p->start_date,
                    'due_on' => $p->end_date,
                    'status' => $p->status,
                    'customer_id' => $p->customer_id,
                    'is_progress_project' => true
                ];
            });
        } else {
            $this->info("Found {$cacheProjects->count()} cached Teamleader projects");
            $projects = $cacheProjects->map(function($p) {
                $p->is_progress_project = false;
                return $p;
            });
        }

        if ($projects->isEmpty()) {
            return collect();
        }

        if ($projectOption) {
            if (is_numeric($projectOption)) {
                $project = $projects->find($projectOption);
            } else {
                $project = $projects->where('title', 'like', "%{$projectOption}%")->first();
            }

            if ($project) {
                $this->info("✓ Selected project: {$project->title}");
                return collect([$project]);
            }
        }

        // Interactive selection
        $this->info("📦 PROJECTS FOR {$customer->name}:");
        $this->table(
            ['#', 'Title', 'Date Range', 'Status'],
            array_merge(
                [[0, 'ALL PROJECTS', '-', '-']],
                $projects->map(function($project, $index) {
                    return [
                        $index + 1,
                        $project->title,
                        ($project->starts_on ? $project->starts_on->format('M Y') : 'N/A') . ' - ' .
                        ($project->due_on ? $project->due_on->format('M Y') : 'N/A'),
                        $project->status
                    ];
                })->toArray()
            )
        );

        $choice = $this->ask('Select project number (0 for all)');

        if ($choice == '0') {
            $this->info("✓ Selected: ALL PROJECTS (" . count($projects) . " total)");
            return $projects;
        }

        if (isset($projects[$choice - 1])) {
            $project = $projects[$choice - 1];
            $this->info("✓ Selected: {$project->title}");
            return collect([$project]);
        }

        return collect();
    }

    protected function determineDateRange()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $months = $this->option('months') ?? 12;

        if ($from && $to) {
            $startDate = Carbon::parse($from);
            $endDate = Carbon::parse($to);
        } else {
            $choices = [
                '1' => ['label' => 'Last 3 months', 'months' => 3],
                '2' => ['label' => 'Last 6 months', 'months' => 6],
                '3' => ['label' => 'Last 1 year', 'months' => 12],
                '4' => ['label' => 'Last 2 years', 'months' => 24],
                '5' => ['label' => 'All time', 'months' => null],
            ];

            $this->info('📅 SELECT DATE RANGE:');
            foreach ($choices as $key => $choice) {
                $this->line("  [{$key}] {$choice['label']}");
            }

            $rangeChoice = $this->ask('Select option', '3'); // Default to 1 year

            if ($rangeChoice == '5') {
                $startDate = Carbon::parse('2015-01-01');
                $endDate = Carbon::now()->endOfMonth();
            } else {
                $selectedMonths = $choices[$rangeChoice]['months'] ?? $months;
                $startDate = Carbon::now()->subMonths($selectedMonths)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            }
        }

        $this->info("✓ Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->newLine();

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    protected function findTimeEntries($customer, $projects, $dateRange)
    {
        $this->info('🔍 SEARCHING FOR TIME ENTRIES...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Get teamleader_id from all projects
        $projectIds = $projects->pluck('teamleader_id')->filter()->toArray();

        $this->line("  Source: " . ($projects->first()->is_progress_project ?? false ? "Progress Projects (imported)" : "Teamleader Projects (cache)"));
        $this->line("  Projects to search: " . count($projectIds));

        // Get all tasks for these projects (only from cache - Progress doesn't have Teamleader task IDs)
        $taskIds = TeamleaderTask::whereIn('teamleader_project_id', $projectIds)
            ->pluck('teamleader_id')
            ->toArray();

        $this->line("  Tasks to search: " . count($taskIds));
        $this->newLine();

        $bar = $this->output->createProgressBar(3);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        // Strategy 1: Find entries linked to projects
        $bar->setMessage('Searching project-linked entries...');
        $projectEntries = TeamleaderTimeEntry::whereIn('teamleader_project_id', $projectIds)
            ->whereBetween('date', [$dateRange['start']->format('Y-m-d'), $dateRange['end']->format('Y-m-d')])
            ->get();
        $bar->advance();

        // Strategy 2: Find entries linked to tasks
        $bar->setMessage('Searching task-linked entries...');
        $taskEntries = TeamleaderTimeEntry::where(function($query) use ($taskIds) {
            foreach ($taskIds as $taskId) {
                $query->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.work_type.id')) = ?", [$taskId]);
            }
        })
        ->whereBetween('date', [$dateRange['start']->format('Y-m-d'), $dateRange['end']->format('Y-m-d')])
        ->get();
        $bar->advance();

        // Strategy 3: Find unlinked entries by date range
        $bar->setMessage('Searching date-based entries...');
        $dateBasedEntries = collect();

        foreach ($projects as $project) {
            if ($project->starts_on && $project->due_on) {
                $projectStart = max($project->starts_on, $dateRange['start']);
                $projectEnd = min($project->due_on, $dateRange['end']);

                $entries = TeamleaderTimeEntry::whereBetween('date', [
                    $projectStart->format('Y-m-d'),
                    $projectEnd->format('Y-m-d')
                ])
                ->whereNull('teamleader_project_id')
                ->get();

                $dateBasedEntries = $dateBasedEntries->merge($entries);
            }
        }
        $bar->advance();

        $bar->finish();
        $this->newLine(2);

        // Merge all results
        return $projectEntries
            ->merge($taskEntries)
            ->merge($dateBasedEntries)
            ->unique('teamleader_id');
    }

    protected function displayResults($customer, $projects, $dateRange, $entries)
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 RESULTS SUMMARY');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $totalHours = round($entries->sum('duration_seconds') / 3600, 2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total unique entries found', $entries->count()],
                ['Total hours', number_format($totalHours, 2)],
                ['Date range', $dateRange['start']->format('Y-m-d') . ' to ' . $dateRange['end']->format('Y-m-d')],
                ['Customer', $customer->name],
                ['Projects', $projects->count()],
            ]
        );

        $this->newLine();

        // Group by user
        $byUser = $entries->groupBy(function($entry) {
            return $entry->raw_data['user']['id'] ?? 'unknown';
        });

        $this->info('👥 ENTRIES BY USER:');
        $userData = $byUser->map(function($entries, $userId) {
            $hours = round($entries->sum('duration_seconds') / 3600, 2);
            $userName = ($entries->first()->raw_data['user']['first_name'] ?? '') . ' ' .
                       ($entries->first()->raw_data['user']['last_name'] ?? 'Unknown');

            return [
                $userName,
                substr($userId, 0, 12) . '...',
                $entries->count(),
                number_format($hours, 2)
            ];
        })->values();

        $this->table(['User', 'Teamleader ID', 'Entries', 'Hours'], $userData);

        $this->newLine();

        // Group by project
        $linkedCount = $entries->filter(fn($e) => !empty($e->teamleader_project_id))->count();
        $unlinkedCount = $entries->count() - $linkedCount;

        $this->line("  Entries already linked to projects: {$linkedCount}");
        $this->line("  Unlinked entries (candidates for manual linking): {$unlinkedCount}");
    }

    protected function exportResults($customer, $entries)
    {
        $this->newLine();
        $this->info('💾 EXPORTING RESULTS...');

        $csvPath = "/tmp/time_entries_" . str_replace(' ', '_', $customer->name) . "_" . date('Y-m-d_H-i-s') . ".csv";
        $csv = fopen($csvPath, 'w');

        // Headers
        fputcsv($csv, [
            'Date',
            'User',
            'Duration (hours)',
            'Description',
            'Project (if linked)',
            'Task (if linked)',
            'Entry ID'
        ]);

        foreach ($entries as $entry) {
            $userName = ($entry->raw_data['user']['first_name'] ?? '') . ' ' . ($entry->raw_data['user']['last_name'] ?? '');
            $hours = round($entry->duration_seconds / 3600, 2);
            $description = $entry->raw_data['description'] ?? '';

            $projectName = '';
            if ($entry->teamleader_project_id) {
                $project = TeamleaderProject::where('teamleader_id', $entry->teamleader_project_id)->first();
                $projectName = $project ? $project->title : 'Unknown';
            }

            $taskName = '';
            if (isset($entry->raw_data['work_type']['type']) && $entry->raw_data['work_type']['type'] === 'todo') {
                $taskName = 'Task: ' . ($entry->raw_data['work_type']['id'] ?? 'unknown');
            }

            fputcsv($csv, [
                $entry->date,
                $userName,
                $hours,
                $description,
                $projectName,
                $taskName,
                $entry->teamleader_id
            ]);
        }

        fclose($csv);

        $this->info("✅ CSV exported to: {$csvPath}");
        $this->line("   Total entries: " . $entries->count());

        // Also save just the IDs
        $idsPath = "/tmp/time_entry_ids_" . date('Y-m-d_H-i-s') . ".txt";
        file_put_contents($idsPath, $entries->pluck('teamleader_id')->implode("\n"));

        $this->info("✅ Entry IDs saved to: {$idsPath}");
    }
}
