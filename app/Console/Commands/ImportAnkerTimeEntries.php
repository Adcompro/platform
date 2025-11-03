<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeamleaderTimeEntry;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\TeamleaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportAnkerTimeEntries extends Command
{
    protected $signature = 'anker:import-time-entries
                            {--dry-run : Run without actually importing}
                            {--user= : User ID to assign entries to (default: first admin)}';

    protected $description = 'Import Anker time entries from Teamleader cache to Progress (API-based milestone/task matching)';

    public function handle()
    {
        $this->info('🎯 IMPORTING ANKER TIME ENTRIES TO PROGRESS');
        $this->info('New Strategy: API-based task + milestone fetching');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No data will be imported');
            $this->newLine();
        }

        // Step 1: Get Anker project mappings
        $ankerProjects = [
            '513855fb-61b7-0bd5-b46f-7f78243899c0' => ['id' => 791, 'name' => 'Anker Aug 2025', 'month' => 8],
            'b3966960-0f73-0a9e-ac6c-1d32ef38b82f' => ['id' => 793, 'name' => 'Anker Sept 2025', 'month' => 9],
            'f8edfe39-c178-0c68-8e66-a192a23913f8' => ['id' => 792, 'name' => 'Anker Oct 2025', 'month' => 10],
        ];

        $this->info('📦 Anker Projects:');
        foreach ($ankerProjects as $tlId => $projectData) {
            // Verify project exists
            $project = Project::find($projectData['id']);
            if ($project) {
                $this->line("  ✓ {$projectData['name']} (ID: {$projectData['id']})");
            } else {
                $this->error("  ✗ {$projectData['name']} (ID: {$projectData['id']}) - NOT FOUND!");
                return 1;
            }
        }
        $this->newLine();

        // Step 2: Get the Anker user from time entries
        $ankerUserId = 'ebe299fa-36a0-0cf9-b054-e7ca9db33f81';

        $this->info('🔍 Finding Anker time entries in cache...');
        $cacheEntries = TeamleaderTimeEntry::whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.user.id')) = ?",
            [$ankerUserId]
        )
        ->whereBetween('date', ['2025-08-01', '2025-10-31'])
        ->orderBy('date')
        ->get();

        $this->line("  Found: {$cacheEntries->count()} entries");
        $this->newLine();

        // Step 3: Get or create Progress user for imports
        $userId = $this->option('user');
        if ($userId) {
            $adminUser = User::find($userId);
            if (!$adminUser) {
                $this->error("User ID {$userId} not found!");
                return 1;
            }
        } else {
            $adminUser = User::where('role', 'super_admin')->orWhere('role', 'admin')->first();
        }

        if (!$adminUser) {
            $this->error('No admin user found! Cannot import.');
            return 1;
        }

        $this->info("👤 Using user: {$adminUser->name} (ID: {$adminUser->id})");
        $this->newLine();

        // Step 4: Show summary before import
        $byMonth = $cacheEntries->groupBy(function($entry) {
            return Carbon::parse($entry->date)->month;
        });

        $this->info('📊 Entries by month:');
        foreach ($byMonth as $month => $entries) {
            $totalHours = round($entries->sum('duration_seconds') / 3600, 2);

            // Find correct project for this month
            $targetProject = null;
            foreach ($ankerProjects as $tlId => $projectData) {
                if ($projectData['month'] == $month) {
                    $targetProject = $projectData;
                    break;
                }
            }

            if ($targetProject) {
                $this->line("  • {$targetProject['name']}: {$entries->count()} entries, {$totalHours} hours");
            }
        }
        $this->newLine();

        if (!$isDryRun) {
            if (!$this->confirm('Do you want to proceed with import?', true)) {
                $this->warn('Import cancelled.');
                return 0;
            }
            $this->newLine();
        }

        // Step 5: Import entries
        $this->info('📥 ' . ($isDryRun ? 'Simulating' : 'Starting') . ' import...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $bar = $this->output->createProgressBar($cacheEntries->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $apiMatched = 0;
        $fallbackUsed = 0;

        foreach ($cacheEntries as $cacheEntry) {
            $date = Carbon::parse($cacheEntry->date);
            $month = $date->month;

            // Determine which Anker project based on month
            $targetProject = null;
            foreach ($ankerProjects as $tlId => $projectData) {
                if ($projectData['month'] == $month) {
                    $targetProject = $projectData;
                    break;
                }
            }

            if (!$targetProject) {
                $bar->setMessage("Skipping {$date->format('Y-m-d')} - no project");
                $skipped++;
                $bar->advance();
                continue;
            }

            // Check if already imported (by teamleader_id for accurate duplicate detection)
            $existing = TimeEntry::where('teamleader_id', $cacheEntry->teamleader_id)->first();

            if ($existing) {
                $bar->setMessage("Skipping {$date->format('Y-m-d')} - exists (ID: " . substr($cacheEntry->teamleader_id, 0, 8) . "...)");
                $skipped++;
                $bar->advance();
                continue;
            }

            if (!$isDryRun) {
                try {
                    // Get project to retrieve company_id
                    $project = Project::find($targetProject['id']);

                    if (!$project) {
                        $bar->setMessage("Error: Project {$targetProject['id']} not found");
                        $errors++;
                        $bar->advance();
                        continue;
                    }

                    // ============================================
                    // NEW API-BASED MATCHING LOGIC (Option A)
                    // ============================================
                    $rawData = $cacheEntry->raw_data;
                    $milestoneId = null;
                    $taskId = null;
                    $matchStatus = 'fallback';
                    $milestoneName = null;
                    $taskName = null;

                    // Check if time entry has a subject (task)
                    if (isset($rawData['subject']) &&
                        isset($rawData['subject']['type']) &&
                        $rawData['subject']['type'] === 'todo' &&
                        isset($rawData['subject']['id'])) {

                        $teamleaderTaskId = $rawData['subject']['id'];

                        try {
                            // STEP 1: Fetch task info from Teamleader API
                            $taskResponse = TeamleaderService::getTask($teamleaderTaskId);
                            $taskData = $taskResponse['data'] ?? $taskResponse;

                            if ($taskData && isset($taskData['title'])) {
                                $taskName = $taskData['title'];

                                // STEP 2: Get milestone ID from task
                                $milestoneTeamleaderId = null;
                                if (isset($taskData['milestone']) && isset($taskData['milestone']['id'])) {
                                    $milestoneTeamleaderId = $taskData['milestone']['id'];
                                }

                                // STEP 3: Find or create milestone in the ANKER project
                                if ($milestoneTeamleaderId) {
                                    // Try to find existing milestone by teamleader_id in raw_data
                                    $milestone = ProjectMilestone::where('project_id', $targetProject['id'])
                                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.teamleader_milestone_id')) = ?",
                                            [$milestoneTeamleaderId])
                                        ->first();

                                    // If not found, create new milestone in Anker project
                                    if (!$milestone) {
                                        // Fetch milestone info from Teamleader API to get the REAL name
                                        $milestoneName = "Milestone " . substr($milestoneTeamleaderId, 0, 8);
                                        $milestoneData = null;

                                        try {
                                            $milestoneResponse = TeamleaderService::getMilestone($milestoneTeamleaderId);
                                            $milestoneData = $milestoneResponse['data'] ?? $milestoneResponse;

                                            if (isset($milestoneData['name'])) {
                                                $milestoneName = $milestoneData['name'];
                                            }
                                        } catch (\Exception $e) {
                                            Log::warning("Failed to fetch milestone name from API", [
                                                'milestone_id' => $milestoneTeamleaderId,
                                                'error' => $e->getMessage()
                                            ]);
                                        }

                                        $milestone = ProjectMilestone::create([
                                            'project_id' => $targetProject['id'],
                                            'name' => $milestoneName,
                                            'description' => 'Auto-created from Teamleader task import',
                                            'status' => 'in_progress',
                                            'sort_order' => 500,
                                            'fee_type' => 'in_fee',
                                            'pricing_type' => 'hourly_rate',
                                            'source_type' => 'manual',
                                            'raw_data' => json_encode([
                                                'teamleader_milestone_id' => $milestoneTeamleaderId,
                                                'imported_from_task' => $teamleaderTaskId,
                                                'import_date' => now()->toDateTimeString()
                                            ])
                                        ]);

                                        Log::info("Created milestone in Anker project", [
                                            'milestone_id' => $milestone->id,
                                            'milestone_name' => $milestoneName,
                                            'teamleader_milestone_id' => $milestoneTeamleaderId,
                                            'anker_project' => $targetProject['name']
                                        ]);
                                    }

                                    $milestoneId = $milestone->id;
                                    $milestoneName = $milestone->name;

                                    // STEP 4: Find or create task in that milestone
                                    $task = ProjectTask::where('project_milestone_id', $milestoneId)
                                        ->where('name', $taskName)
                                        ->first();

                                    if (!$task) {
                                        $task = ProjectTask::create([
                                            'project_milestone_id' => $milestoneId,
                                            'name' => $taskName,
                                            'description' => $taskData['description'] ?? 'Auto-created from Teamleader',
                                            'status' => 'in_progress',
                                            'sort_order' => 100,
                                            'fee_type' => 'in_fee',
                                            'pricing_type' => 'hourly_rate',
                                            'source_type' => 'manual',
                                            'raw_data' => json_encode([
                                                'teamleader_task_id' => $teamleaderTaskId,
                                                'import_date' => now()->toDateTimeString()
                                            ])
                                        ]);

                                        Log::info("Created task in Anker milestone", [
                                            'task_id' => $task->id,
                                            'task_name' => $taskName,
                                            'milestone_name' => $milestoneName,
                                            'anker_project' => $targetProject['name']
                                        ]);
                                    }

                                    $taskId = $task->id;
                                    $matchStatus = 'api_matched';
                                    $apiMatched++;
                                }
                            }
                        } catch (\Exception $e) {
                            // API call failed, fallback to generic
                            Log::warning("Failed to fetch task from API", [
                                'task_id' => $teamleaderTaskId,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // FALLBACK: If no match found, use generic milestone/task
                    if (!$milestoneId || !$taskId) {
                        $milestone = ProjectMilestone::firstOrCreate(
                            [
                                'project_id' => $targetProject['id'],
                                'name' => '7. Imported Time Entries'
                            ],
                            [
                                'description' => 'Automatically created for time entries imported from Teamleader without specific task assignment',
                                'status' => 'in_progress',
                                'sort_order' => 999,
                                'fee_type' => 'in_fee',
                                'pricing_type' => 'hourly_rate',
                                'source_type' => 'manual'
                            ]
                        );

                        $task = ProjectTask::firstOrCreate(
                            [
                                'project_milestone_id' => $milestone->id,
                                'name' => 'General Project Work'
                            ],
                            [
                                'description' => 'Imported time entries from Teamleader without specific task assignment',
                                'status' => 'in_progress',
                                'sort_order' => 1,
                                'fee_type' => 'in_fee',
                                'pricing_type' => 'hourly_rate',
                                'source_type' => 'manual'
                            ]
                        );

                        $milestoneId = $milestone->id;
                        $taskId = $task->id;
                        $matchStatus = 'fallback';
                        $fallbackUsed++;
                    }

                    // Calculate hours and minutes
                    $totalMinutes = round($cacheEntry->duration_seconds / 60);
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;

                    // Extract hourly rate from time entry
                    $company = \App\Models\Company::find($project->company_id);
                    $hourlyRate = isset($rawData['hourly_rate']['amount'])
                        ? (float) $rawData['hourly_rate']['amount']
                        : ($company->default_hourly_rate ?? 150.00);

                    // Build description
                    if (!empty($rawData['description'])) {
                        $description = $rawData['description'];
                    } elseif ($matchStatus === 'api_matched' && $taskName) {
                        $description = "Work on: {$taskName}";
                    } elseif (!empty($cacheEntry->description)) {
                        $description = $cacheEntry->description;
                    } else {
                        $description = 'Time entry from Teamleader (no description)';
                    }

                    // Find the actual user from Teamleader (1-op-1 mapping)
                    $teamleaderUserId = $rawData['user']['id'] ?? null;
                    $actualUser = null;

                    if ($teamleaderUserId) {
                        $actualUser = User::where('teamleader_id', $teamleaderUserId)->first();
                    }

                    // Fallback to admin user if Teamleader user not found in Progress
                    if (!$actualUser) {
                        Log::warning("Teamleader user not found in Progress", [
                            'teamleader_user_id' => $teamleaderUserId,
                            'entry_date' => $cacheEntry->date,
                            'falling_back_to_admin' => $adminUser->id
                        ]);
                        $actualUser = $adminUser;
                    }

                    // Create time entry
                    TimeEntry::create([
                        'user_id' => $actualUser->id,  // Use actual Teamleader user!
                        'company_id' => $project->company_id,
                        'customer_id' => $project->customer_id,
                        'project_id' => $targetProject['id'],
                        'project_milestone_id' => $milestoneId,
                        'project_task_id' => $taskId,
                        'entry_date' => $cacheEntry->date,
                        'hours' => $hours,
                        'minutes' => $minutes,
                        'description' => $description,
                        'hourly_rate_used' => $hourlyRate,
                        'is_billable' => 'billable',
                        'status' => 'approved',
                        'approved_by' => $adminUser->id,
                        'approved_at' => now(),
                        'created_at' => $cacheEntry->created_at ?? now(),
                        'updated_at' => now(),
                    ]);

                    $statusIcon = $matchStatus === 'api_matched' ? '✓' : '↻';
                    $bar->setMessage("Imported {$date->format('Y-m-d')} {$statusIcon} → {$targetProject['name']}");
                    $imported++;

                } catch (\Exception $e) {
                    $bar->setMessage("Error: {$date->format('Y-m-d')}");
                    $this->newLine();
                    $this->error("  Error importing {$date->format('Y-m-d')}: {$e->getMessage()}");
                    $errors++;
                }
            } else {
                $totalMinutes = round($cacheEntry->duration_seconds / 60);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                $bar->setMessage("Would import {$date->format('Y-m-d')} ({$hours}h {$minutes}m)");
                $imported++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ IMPORT ' . ($isDryRun ? 'SIMULATION ' : '') . 'COMPLETED!');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported', $imported],
                ['API Matched (✓)', $apiMatched],
                ['Fallback Used (↻)', $fallbackUsed],
                ['Skipped (already exist)', $skipped],
                ['Errors', $errors],
                ['Total processed', $cacheEntries->count()],
            ]
        );

        // Verify import (only if not dry run)
        if (!$isDryRun && $imported > 0) {
            $this->newLine();
            $this->info('🔍 Verification:');

            $verificationData = [];
            foreach ($ankerProjects as $tlId => $projectData) {
                $count = TimeEntry::where('project_id', $projectData['id'])
                    ->whereBetween('entry_date', ['2025-08-01', '2025-10-31'])
                    ->count();

                $totalHours = TimeEntry::where('project_id', $projectData['id'])
                    ->whereBetween('entry_date', ['2025-08-01', '2025-10-31'])
                    ->get()
                    ->sum(function($entry) {
                        return $entry->hours + ($entry->minutes / 60);
                    });

                $verificationData[] = [
                    $projectData['name'],
                    $count,
                    number_format($totalHours, 2)
                ];
            }

            $this->table(['Project', 'Entries', 'Total Hours'], $verificationData);
        }

        $this->newLine();
        $this->info('✨ Done!');

        return 0;
    }
}
