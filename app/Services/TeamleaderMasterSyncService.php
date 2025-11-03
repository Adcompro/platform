<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Master Sync Service - Synchroniseert ALLE data van Teamleader naar cache
 *
 * Deze service zorgt voor een complete mirror van Teamleader data in onze database.
 * Alle relaties en foreign keys worden correct opgeslagen zodat we later selectief
 * kunnen importeren naar Progress.
 *
 * Sync volgorde:
 * 1. Companies (basis)
 * 2. Contacts (gekoppeld aan companies)
 * 3. Projects (gekoppeld aan companies)
 * 4. Milestones (gekoppeld aan projects)
 * 5. Tasks (gekoppeld aan milestones)
 * 6. Time Entries (gekoppeld aan projects/milestones/tasks)
 */
class TeamleaderMasterSyncService
{
    /**
     * Voer een COMPLETE sync uit van alle Teamleader data
     *
     * @return array Statistics van de volledige sync
     */
    public function syncAll(): array
    {
        $startTime = microtime(true);

        $results = [
            'started_at' => now(),
            'companies' => [],
            'contacts' => [],
            'projects' => [],
            'milestones' => [],
            'tasks' => [],
            'time_entries' => [],
            'duration' => 0,
            'success' => false
        ];

        Log::info('═══════════════════════════════════════════════════════');
        Log::info('🚀 STARTING MASTER SYNC - Complete Teamleader Data Mirror');
        Log::info('═══════════════════════════════════════════════════════');

        try {
            // STAP 1: Sync Companies
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "📋 STEP 1/6: Syncing Companies\n";
            echo str_repeat('═', 60) . "\n";
            $results['companies'] = $this->syncCompanies();
            echo "✅ Companies synced: {$results['companies']['synced']}\n";

            // STAP 2: Sync Contacts
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "👥 STEP 2/6: Syncing Contacts\n";
            echo str_repeat('═', 60) . "\n";
            $results['contacts'] = $this->syncContacts();
            echo "✅ Contacts synced: {$results['contacts']['synced']}\n";

            // STAP 3: Sync Projects
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "📁 STEP 3/6: Syncing Projects\n";
            echo str_repeat('═', 60) . "\n";
            $results['projects'] = $this->syncProjects();
            echo "✅ Projects synced: {$results['projects']['synced']}\n";

            // STAP 4: Sync Milestones
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "🎯 STEP 4/6: Syncing Milestones\n";
            echo str_repeat('═', 60) . "\n";
            $results['milestones'] = $this->syncMilestones();
            echo "✅ Milestones synced: {$results['milestones']['synced']}\n";

            // STAP 5: Sync Tasks
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "✔️ STEP 5/6: Syncing Tasks\n";
            echo str_repeat('═', 60) . "\n";
            $results['tasks'] = $this->syncTasks();
            echo "✅ Tasks synced: {$results['tasks']['synced']}\n";

            // STAP 6: Sync Time Entries
            echo "\n" . str_repeat('═', 60) . "\n";
            echo "⏱️ STEP 6/6: Syncing Time Entries\n";
            echo str_repeat('═', 60) . "\n";
            $results['time_entries'] = $this->syncTimeEntries();
            echo "✅ Time entries synced: {$results['time_entries']['synced']}\n";

            $results['success'] = true;
            $results['duration'] = round(microtime(true) - $startTime, 2);

            echo "\n" . str_repeat('═', 60) . "\n";
            echo "🎉 MASTER SYNC COMPLETED!\n";
            echo str_repeat('═', 60) . "\n";
            echo "Duration: {$results['duration']} seconds\n";
            echo "Companies: {$results['companies']['synced']}\n";
            echo "Contacts: {$results['contacts']['synced']}\n";
            echo "Projects: {$results['projects']['synced']}\n";
            echo "Milestones: {$results['milestones']['synced']}\n";
            echo "Tasks: {$results['tasks']['synced']}\n";
            echo "Time Entries: {$results['time_entries']['synced']}\n";
            echo str_repeat('═', 60) . "\n";

            Log::info('Master sync completed successfully', $results);

            return $results;

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
            $results['duration'] = round(microtime(true) - $startTime, 2);

            Log::error('Master sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Sync alle companies van Teamleader naar teamleader_companies cache
     */
    protected function syncCompanies(): array
    {
        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            echo "Fetching companies page {$page}...\n";

            $response = TeamleaderService::listCompanies($page, 100);

            if (empty($response['data'])) {
                break;
            }

            $count = count($response['data']);
            echo "Processing {$count} companies...\n";

            foreach ($response['data'] as $tlCompany) {
                try {
                    // Check of company al bestaat in cache
                    $existing = \App\Models\TeamleaderCompany::where('teamleader_id', $tlCompany['id'])->first();

                    // Parse address data (nested structure!)
                    $addresses = $tlCompany['addresses'] ?? [];
                    $address = null;
                    if (!empty($addresses) && isset($addresses[0]['address'])) {
                        $address = $addresses[0]['address'];
                    }

                    $companyData = [
                        'name' => $tlCompany['name'] ?? 'Unnamed Company',
                        'vat_number' => $tlCompany['vat_number'] ?? null,
                        'emails' => !empty($tlCompany['emails']) ? json_encode($tlCompany['emails']) : null,
                        'website' => $tlCompany['website'] ?? null,
                        'line_1' => $address['line_1'] ?? null,
                        'line_2' => $address['line_2'] ?? null,
                        'postal_code' => $address['postal_code'] ?? null,
                        'city' => $address['city'] ?? null,
                        'country' => $address['country'] ?? null,
                        'status' => $tlCompany['status'] ?? 'active',
                        'raw_data' => $tlCompany,
                        'synced_at' => now(),
                    ];

                    if ($existing) {
                        $existing->update($companyData);
                        $stats['updated']++;
                    } else {
                        \App\Models\TeamleaderCompany::create(array_merge(
                            ['teamleader_id' => $tlCompany['id']],
                            $companyData
                        ));
                        $stats['synced']++;
                    }

                } catch (\Exception $e) {
                    $stats['failed']++;
                    Log::error('Failed to sync company', [
                        'company_id' => $tlCompany['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($count < 100) {
                $hasMore = false;
            } else {
                $page++;
                usleep(350000); // Rate limiting
            }
        }

        return $stats;
    }

    /**
     * Sync alle contacts van Teamleader naar teamleader_contacts cache
     */
    protected function syncContacts(): array
    {
        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            echo "Fetching contacts page {$page}...\n";

            $response = TeamleaderService::listContacts($page, 100);

            if (empty($response['data'])) {
                break;
            }

            $count = count($response['data']);
            echo "Processing {$count} contacts...\n";

            foreach ($response['data'] as $tlContact) {
                try {
                    // Check of contact al bestaat in cache
                    $existing = \App\Models\TeamleaderContact::where('teamleader_id', $tlContact['id'])->first();

                    // Parse address data (nested structure!)
                    $addresses = $tlContact['addresses'] ?? [];
                    $address = null;
                    if (!empty($addresses) && isset($addresses[0]['address'])) {
                        $address = $addresses[0]['address'];
                    }

                    // Extract email
                    $email = null;
                    if (!empty($tlContact['emails'])) {
                        $email = $tlContact['emails'][0]['email'] ?? null;
                    }

                    // Extract phone numbers
                    $phone = null;
                    $mobile = null;
                    if (!empty($tlContact['telephones'])) {
                        foreach ($tlContact['telephones'] as $telephone) {
                            $type = $telephone['type'] ?? 'phone';
                            $number = $telephone['number'] ?? null;

                            if ($type === 'mobile' && !$mobile) {
                                $mobile = $number;
                            } elseif ($type === 'phone' && !$phone) {
                                $phone = $number;
                            }
                        }
                    }

                    // Extract companies (JSON array van company IDs)
                    $companies = [];
                    if (!empty($tlContact['companies'])) {
                        foreach ($tlContact['companies'] as $companyLink) {
                            if (isset($companyLink['company']['id'])) {
                                $companies[] = $companyLink['company']['id'];
                            }
                        }
                    }

                    $contactData = [
                        'first_name' => $tlContact['first_name'] ?? null,
                        'last_name' => $tlContact['last_name'] ?? null,
                        'full_name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                        'email' => $email,
                        'phone' => $phone,
                        'mobile' => $mobile,
                        'position' => $tlContact['position'] ?? null,
                        'language' => $tlContact['language'] ?? null,
                        'companies' => !empty($companies) ? json_encode($companies) : null,
                        'line_1' => $address['line_1'] ?? null,
                        'line_2' => $address['line_2'] ?? null,
                        'postal_code' => $address['postal_code'] ?? null,
                        'city' => $address['city'] ?? null,
                        'country' => $address['country'] ?? null,
                        'raw_data' => $tlContact,
                        'synced_at' => now(),
                    ];

                    if ($existing) {
                        $existing->update($contactData);
                        $stats['updated']++;
                    } else {
                        \App\Models\TeamleaderContact::create(array_merge(
                            ['teamleader_id' => $tlContact['id']],
                            $contactData
                        ));
                        $stats['synced']++;
                    }

                } catch (\Exception $e) {
                    $stats['failed']++;
                    Log::error('Failed to sync contact', [
                        'contact_id' => $tlContact['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($count < 100) {
                $hasMore = false;
            } else {
                $page++;
                usleep(350000);
            }
        }

        return $stats;
    }

    /**
     * Sync alle projects van Teamleader naar teamleader_projects cache
     */
    protected function syncProjects(): array
    {
        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            echo "Fetching projects page {$page}...\n";

            $response = TeamleaderService::listProjects($page, 100);

            if (empty($response['data'])) {
                break;
            }

            $count = count($response['data']);
            echo "Processing {$count} projects...\n";

            foreach ($response['data'] as $tlProject) {
                try {
                    // Check of project al bestaat in cache
                    $existing = \App\Models\TeamleaderProject::where('teamleader_id', $tlProject['id'])->first();

                    // Extract customer (company) ID
                    $customerId = null;
                    if (isset($tlProject['customer']['type']) && $tlProject['customer']['type'] === 'company') {
                        $customerId = $tlProject['customer']['id'] ?? null;
                    }

                    // Extract budget
                    $budgetAmount = null;
                    if (isset($tlProject['purchase_order_number'])) {
                        // Budget kan in verschillende velden zitten, check raw data
                        $budgetAmount = $tlProject['budget']['amount'] ?? null;
                    }

                    $projectData = [
                        'customer_id' => $customerId, // Teamleader company ID
                        'title' => $tlProject['title'] ?? 'Untitled Project',
                        'description' => $tlProject['description'] ?? null,
                        'status' => $tlProject['status'] ?? 'active',
                        'starts_on' => $tlProject['starts_on'] ?? null,
                        'due_on' => $tlProject['due_on'] ?? null,
                        'budget_amount' => $budgetAmount,
                        'raw_data' => $tlProject,
                        'synced_at' => now(),
                    ];

                    if ($existing) {
                        $existing->update($projectData);
                        $stats['updated']++;
                    } else {
                        \App\Models\TeamleaderProject::create(array_merge(
                            ['teamleader_id' => $tlProject['id']],
                            $projectData
                        ));
                        $stats['synced']++;
                    }

                } catch (\Exception $e) {
                    $stats['failed']++;
                    Log::error('Failed to sync project', [
                        'project_id' => $tlProject['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($count < 100) {
                $hasMore = false;
            } else {
                $page++;
                usleep(350000);
            }
        }

        return $stats;
    }

    /**
     * Sync alle milestones van Teamleader naar teamleader_milestones cache
     */
    protected function syncMilestones(): array
    {
        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        // Voor elke project in cache, haal milestones op
        $projects = \App\Models\TeamleaderProject::all();

        echo "Found " . $projects->count() . " projects in cache\n";

        foreach ($projects as $project) {
            try {
                $response = TeamleaderService::listMilestonesForProject($project->teamleader_id);

                if (!empty($response['data'])) {
                    $milestoneCount = count($response['data']);
                    echo "Project {$project->title}: {$milestoneCount} milestones\n";

                    foreach ($response['data'] as $tlMilestone) {
                        try {
                            // Check of milestone al bestaat
                            $existing = \App\Models\TeamleaderMilestone::where('teamleader_id', $tlMilestone['id'])->first();

                            $milestoneData = [
                                'teamleader_project_id' => $project->teamleader_id, // KRITIEKE LINK!
                                'title' => $tlMilestone['title'] ?? 'Untitled Milestone',
                                'description' => $tlMilestone['description'] ?? null,
                                'status' => $tlMilestone['status'] ?? 'active',
                                'starts_on' => $tlMilestone['starts_on'] ?? null,
                                'ends_on' => $tlMilestone['ends_on'] ?? null,
                                'responsible_user_id' => $tlMilestone['responsible_user']['id'] ?? null,
                                'raw_data' => $tlMilestone,
                                'synced_at' => now(),
                            ];

                            if ($existing) {
                                $existing->update($milestoneData);
                                $stats['updated']++;
                            } else {
                                \App\Models\TeamleaderMilestone::create(array_merge(
                                    ['teamleader_id' => $tlMilestone['id']],
                                    $milestoneData
                                ));
                                $stats['synced']++;
                            }

                        } catch (\Exception $e) {
                            $stats['failed']++;
                            Log::error('Failed to sync milestone', [
                                'milestone_id' => $tlMilestone['id'] ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                usleep(100000); // Rate limiting
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error('Failed to fetch milestones for project', [
                    'project_id' => $project->teamleader_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $stats;
    }

    /**
     * Sync alle tasks van Teamleader naar teamleader_tasks cache
     */
    protected function syncTasks(): array
    {
        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        // Voor elke milestone in cache, haal tasks op
        $milestones = \App\Models\TeamleaderMilestone::all();

        echo "Found " . $milestones->count() . " milestones in cache\n";

        foreach ($milestones as $milestone) {
            try {
                $response = TeamleaderService::listTasksForMilestone($milestone->teamleader_id);

                if (!empty($response['data'])) {
                    $taskCount = count($response['data']);
                    echo "Milestone {$milestone->title}: {$taskCount} tasks\n";

                    foreach ($response['data'] as $tlTask) {
                        try {
                            // Check of task al bestaat
                            $existing = \App\Models\TeamleaderTask::where('teamleader_id', $tlTask['id'])->first();

                            $taskData = [
                                'teamleader_milestone_id' => $milestone->teamleader_id, // KRITIEKE LINK!
                                'teamleader_project_id' => $milestone->teamleader_project_id, // Extra link naar project
                                'title' => $tlTask['title'] ?? 'Untitled Task',
                                'description' => $tlTask['description'] ?? null,
                                'status' => $tlTask['completed'] ? 'completed' : 'open',
                                'due_on' => $tlTask['due_on'] ?? null,
                                'estimated_duration' => $tlTask['estimated_duration'] ?? null,
                                'completed_at' => $tlTask['completed_at'] ?? null,
                                'raw_data' => $tlTask,
                                'synced_at' => now(),
                            ];

                            if ($existing) {
                                $existing->update($taskData);
                                $stats['updated']++;
                            } else {
                                \App\Models\TeamleaderTask::create(array_merge(
                                    ['teamleader_id' => $tlTask['id']],
                                    $taskData
                                ));
                                $stats['synced']++;
                            }

                        } catch (\Exception $e) {
                            $stats['failed']++;
                            Log::error('Failed to sync task', [
                                'task_id' => $tlTask['id'] ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                usleep(100000); // Rate limiting
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error('Failed to fetch tasks for milestone', [
                    'milestone_id' => $milestone->teamleader_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $stats;
    }

    /**
     * Sync alle time entries van Teamleader
     */
    protected function syncTimeEntries(): array
    {
        // Gebruik bestaande TeamleaderProjectSyncService::syncTimeEntries()
        $syncService = new TeamleaderProjectSyncService();
        return $syncService->syncTimeEntries();
    }
}
