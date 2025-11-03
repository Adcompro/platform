<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecurringProjectService
{
    /**
     * Check welke recurring projects een nieuw project nodig hebben en maak ze aan
     */
    public function processRecurringProjects(): array
    {
        $results = [
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        // Haal alle actieve recurring projects op (alleen de "master" projecten)
        $recurringProjects = Project::where('is_recurring', true)
            ->whereNull('parent_recurring_project_id') // Alleen master projects
            ->where('status', 'active')
            ->get();

        Log::info('Processing recurring projects', ['count' => $recurringProjects->count()]);

        foreach ($recurringProjects as $project) {
            $results['processed']++;

            try {
                // Check of we een nieuw project moeten aanmaken
                if ($this->shouldCreateNextProject($project)) {
                    $newProject = $this->duplicateRecurringProject($project);

                    if ($newProject) {
                        $results['created']++;
                        $results['details'][] = [
                            'action' => 'created',
                            'master_project' => $project->name,
                            'new_project' => $newProject->name,
                            'period' => $newProject->recurring_period
                        ];

                        Log::info('Created recurring project', [
                            'master_id' => $project->id,
                            'new_id' => $newProject->id,
                            'new_name' => $newProject->name
                        ]);
                    } else {
                        $results['errors']++;
                    }
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'action' => 'error',
                    'project' => $project->name,
                    'error' => $e->getMessage()
                ];

                Log::error('Error processing recurring project', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Check of er een nieuw project aangemaakt moet worden
     */
    protected function shouldCreateNextProject(Project $project): bool
    {
        // Check of recurring enabled is
        if (!$project->is_recurring) {
            return false;
        }

        // Check of master project status "active" is
        if ($project->status !== 'active') {
            Log::info('Recurring project not active, skipping', [
                'project_id' => $project->id,
                'status' => $project->status
            ]);
            return false;
        }

        // Check of we nog binnen de end date zijn (als die er is)
        if ($project->recurring_end_date && Carbon::now()->isAfter($project->recurring_end_date)) {
            Log::info('Recurring project past end date', ['project_id' => $project->id]);
            return false;
        }

        // Bepaal wanneer de volgende periode begint
        $nextPeriodStart = $this->calculateNextPeriodStart($project);

        // Check of we al dicht genoeg bij de volgende periode zijn
        $daysUntilNextPeriod = Carbon::now()->diffInDays($nextPeriodStart, false);

        Log::info('Checking if should create next project', [
            'project_id' => $project->id,
            'next_period_start' => $nextPeriodStart->format('Y-m-d'),
            'days_until' => $daysUntilNextPeriod,
            'days_before_threshold' => $project->recurring_days_before
        ]);

        // Als we binnen de "days before" threshold zitten, moeten we een nieuw project aanmaken
        if ($daysUntilNextPeriod <= $project->recurring_days_before) {
            // Check of er al een project bestaat voor deze periode
            $nextPeriod = $this->calculateNextPeriod($project);

            $existingProject = Project::where('parent_recurring_project_id', $project->id)
                ->where('recurring_period', $nextPeriod)
                ->first();

            if ($existingProject) {
                Log::info('Project already exists for this period', [
                    'period' => $nextPeriod,
                    'existing_id' => $existingProject->id
                ]);
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Bereken wanneer de volgende periode begint
     */
    protected function calculateNextPeriodStart(Project $project): Carbon
    {
        // Voor monthly recurring: eerste dag van volgende maand
        if ($project->recurring_frequency === 'monthly') {
            return Carbon::now()->addMonth()->startOfMonth();
        }

        // Voor quarterly: eerste dag van volgend kwartaal
        if ($project->recurring_frequency === 'quarterly') {
            return Carbon::now()->addQuarter()->startOfQuarter();
        }

        // Default: volgende maand
        return Carbon::now()->addMonth()->startOfMonth();
    }

    /**
     * Bereken de periode string voor het volgende project (bijv. "Dec 2025")
     */
    protected function calculateNextPeriod(Project $project): string
    {
        $nextStart = $this->calculateNextPeriodStart($project);

        // Voor monthly: "Dec 2025"
        if ($project->recurring_frequency === 'monthly') {
            return $nextStart->format('M Y');
        }

        // Voor quarterly: "Q4 2025"
        if ($project->recurring_frequency === 'quarterly') {
            $quarter = ceil($nextStart->month / 3);
            return "Q{$quarter} " . $nextStart->year;
        }

        return $nextStart->format('M Y');
    }

    /**
     * Genereer project naam voor volgende periode
     */
    protected function generateNextProjectName(Project $project): string
    {
        $baseName = $project->recurring_base_name ?: $project->name;
        $period = $this->calculateNextPeriod($project);

        return trim($baseName) . ' ' . $period;
    }

    /**
     * Duplicate een recurring project voor de volgende periode
     */
    public function duplicateRecurringProject(Project $masterProject): ?Project
    {
        try {
            DB::beginTransaction();

            // Bereken nieuwe project details
            $nextPeriodStart = $this->calculateNextPeriodStart($masterProject);
            $nextPeriodEnd = $nextPeriodStart->copy()->endOfMonth();
            $newProjectName = $this->generateNextProjectName($masterProject);
            $period = $this->calculateNextPeriod($masterProject);

            Log::info('Duplicating recurring project', [
                'master_id' => $masterProject->id,
                'new_name' => $newProjectName,
                'period' => $period,
                'start' => $nextPeriodStart->format('Y-m-d'),
                'end' => $nextPeriodEnd->format('Y-m-d')
            ]);

            // Maak nieuw project aan (kopie van master, maar niet recurring)
            $newProject = $masterProject->replicate();
            $newProject->teamleader_id = null; // Reset teamleader_id (unique constraint)
            $newProject->name = $newProjectName;
            $newProject->start_date = $nextPeriodStart;
            $newProject->end_date = $nextPeriodEnd;
            $newProject->parent_recurring_project_id = $masterProject->id;
            $newProject->recurring_period = $period;

            // Set recurring_series_id (same as master project)
            // If master doesn't have one yet, create it
            if (!$masterProject->recurring_series_id) {
                $seriesId = 'series-' . $masterProject->id;
                $masterProject->recurring_series_id = $seriesId;
                $masterProject->save();
            }
            $newProject->recurring_series_id = $masterProject->recurring_series_id;

            $newProject->is_recurring = false; // Child projects zijn NIET zelf recurring
            $newProject->recurring_base_name = null;
            $newProject->recurring_end_date = null;
            $newProject->recurring_days_before = null;
            $newProject->status = 'active';
            $newProject->created_by = $masterProject->created_by;
            $newProject->save();

            // Kopieer team members
            $this->duplicateTeamMembers($masterProject, $newProject);

            // Kopieer companies
            $this->duplicateCompanies($masterProject, $newProject);

            // Kopieer milestones met tasks en subtasks
            $this->duplicateMilestones($masterProject, $newProject);

            DB::commit();

            Log::info('Successfully duplicated recurring project', [
                'master_id' => $masterProject->id,
                'new_id' => $newProject->id,
                'new_name' => $newProject->name
            ]);

            return $newProject;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error duplicating recurring project', [
                'master_id' => $masterProject->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Kopieer team members naar nieuw project
     */
    protected function duplicateTeamMembers(Project $source, Project $target): void
    {
        $teamMembers = $source->users()->get();

        foreach ($teamMembers as $user) {
            $target->users()->attach($user->id, [
                'role_override' => $user->pivot->role_override,
                'can_edit_fee' => $user->pivot->can_edit_fee,
                'can_view_financials' => $user->pivot->can_view_financials,
                'can_log_time' => $user->pivot->can_log_time,
                'can_approve_time' => $user->pivot->can_approve_time,
                'added_by' => $user->pivot->added_by,
                'added_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('Duplicated team members', [
            'target_project_id' => $target->id,
            'count' => $teamMembers->count()
        ]);
    }

    /**
     * Kopieer companies naar nieuw project
     */
    protected function duplicateCompanies(Project $source, Project $target): void
    {
        $companies = $source->companies()->get();

        foreach ($companies as $company) {
            $target->companies()->attach($company->id, [
                'role' => $company->pivot->role,
                'billing_method' => $company->pivot->billing_method,
                'billing_start_date' => $target->start_date,
                'hourly_rate' => $company->pivot->hourly_rate,
                'fixed_amount' => $company->pivot->fixed_amount,
                'hourly_rate_override' => $company->pivot->hourly_rate_override,
                'monthly_fixed_amount' => $company->pivot->monthly_fixed_amount,
                'is_active' => $company->pivot->is_active,
                'notes' => $company->pivot->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('Duplicated companies', [
            'target_project_id' => $target->id,
            'count' => $companies->count()
        ]);
    }

    /**
     * Kopieer milestones, tasks en subtasks naar nieuw project
     */
    protected function duplicateMilestones(Project $source, Project $target): void
    {
        // Note: Subtasks zijn momenteel niet geïmplementeerd in database (geen project_subtasks table)
        // Als die in de toekomst worden toegevoegd, kan de with() en loop hieronder ge-enabled worden
        $milestones = $source->milestones()->with(['tasks'])->orderBy('sort_order')->get();

        foreach ($milestones as $milestone) {
            // Maak nieuwe milestone aan
            $newMilestone = $milestone->replicate();
            $newMilestone->project_id = $target->id;
            $newMilestone->status = 'pending'; // Reset status voor nieuwe periode
            $newMilestone->save();

            // Kopieer tasks voor deze milestone
            foreach ($milestone->tasks as $task) {
                $newTask = $task->replicate();
                $newTask->project_milestone_id = $newMilestone->id;
                $newTask->status = 'pending'; // Reset status
                $newTask->save();

                // TODO: Subtasks kopiëren zodra project_subtasks table is aangemaakt
                // foreach ($task->subtasks as $subtask) {
                //     $newSubtask = $subtask->replicate();
                //     $newSubtask->project_task_id = $newTask->id;
                //     $newSubtask->status = 'pending';
                //     $newSubtask->save();
                // }
            }
        }

        Log::info('Duplicated milestones structure', [
            'target_project_id' => $target->id,
            'milestones_count' => $milestones->count()
        ]);
    }
}
