<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'sku_code',
        'total_price',
        'estimated_hours',
        'is_package',
        'is_active',
        'is_public',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_price' => 'float',
        'estimated_hours' => 'float',
        'is_package' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    /**
     * Service behoort tot een company
     */
    public function companyRelation(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Service heeft milestones
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ServiceMilestone::class)->orderBy('sort_order');
    }

    /**
     * Service heeft taken (via milestones)
     */
    public function tasks()
    {
        return $this->hasManyThrough(ServiceTask::class, ServiceMilestone::class);
    }

    /**
     * Service heeft subtaken (via milestones en taken)
     */
    public function subtasks()
    {
        return $this->hasManyThrough(
            ServiceSubtask::class,
            ServiceTask::class,
            'service_milestone_id',  // Foreign key op ServiceTask table
            'service_task_id',       // Foreign key op ServiceSubtask table
            'id',                    // Local key op Service table
            'service_milestone_id'   // Local key op ServiceMilestone table
        );
    }

    /**
     * Service usage in projects (when implemented)
     */
    // public function projectServices(): HasMany
    // {
    //     return $this->hasMany(ProjectService::class);
    // }

    /**
     * User die service aanmaakte
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User die service laatst bewerkte
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all activities for this service
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ServiceActivity::class);
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope voor actieve services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope voor publieke services
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope voor services van een bepaalde company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope voor service packages
     */
    public function scopePackages($query)
    {
        return $query->where('is_package', true);
    }

    /**
     * Scope voor individuele services (geen packages)
     */
    public function scopeIndividual($query)
    {
        return $query->where('is_package', false);
    }

    /**
     * Scope voor services met een bepaalde status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // =====================================
    // COMPUTED ATTRIBUTES - ✅ FIXED
    // =====================================

    /**
     * Get status badge class voor UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get service type display
     */
    public function getTypeDisplayAttribute(): string
    {
        return $this->is_package ? 'Package' : 'Individual Service';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '€ ' . number_format($this->total_price, 2, ',', '.');
    }

    /**
     * Get total milestones count - ✅ FIXED
     */
    public function getTotalMilestonesAttribute(): int
    {
        return $this->milestones()->count();
    }

    /**
     * Get total tasks count - ✅ FIXED
     */
    public function getTotalTasksAttribute(): int
    {
        // Use relationship counting to avoid SQL ambiguity
        return $this->milestones->sum(function($milestone) {
            return $milestone->tasks->count();
        });
    }

    /**
     * Get total subtasks count - ✅ FIXED
     */
    public function getTotalSubtasksAttribute(): int
    {
        return $this->milestones->sum(function($milestone) {
            return $milestone->tasks->sum(function($task) {
                return $task->subtasks->count();
            });
        });
    }

    /**
 * Get total estimated hours from structure - ✅ EXTRA SAFE
 */
public function getTotalStructureHoursAttribute(): float
{
    try {
        $totalHours = 0;
        
        // Safety check for loaded relationships
        if (!$this->relationLoaded('milestones')) {
            $this->load('milestones.tasks.subtasks');
        }
        
        foreach ($this->milestones as $milestone) {
            $totalHours += (float)($milestone->estimated_hours ?? 0);
            
            foreach ($milestone->tasks as $task) {
                $totalHours += (float)($task->estimated_hours ?? 0);
                
                foreach ($task->subtasks as $subtask) {
                    $totalHours += (float)($subtask->estimated_hours ?? 0);
                }
            }
        }
        
        return round($totalHours, 2);
    } catch (\Exception $e) {
        // Return 0 if any error occurs
        return 0.0;
    }
}
    /**
 * Get hourly rate (calculated from total_price and estimated_hours) - ✅ FIXED
 */
public function getCalculatedHourlyRateAttribute(): float
{
    // ✅ FIXED: Check for zero division BEFORE calculating
    if (($this->estimated_hours ?? 0) > 0 && ($this->total_price ?? 0) > 0) {
        return round($this->total_price / $this->estimated_hours, 2);
    }
    
    return 0.0;
}

    // =====================================
    // BUSINESS LOGIC METHODS
    // =====================================

    /**
     * Bereken en update de totale estimated hours van alle milestones
     */
    public function calculateAndUpdateEstimatedHours(): void
    {
        // Eerst update alle milestone uren
        foreach ($this->milestones as $milestone) {
            $milestone->calculateAndUpdateEstimatedHours();
        }
        
        // Dan tel alle milestone uren op voor de service
        $this->estimated_hours = $this->calculateEstimatedHours();
        $this->save();
    }

    /**
     * Bereken estimated hours zonder op te slaan (voor preview)
     * Dit is de som van alle milestone estimated_hours
     */
    public function calculateEstimatedHours(): float
    {
        // Zorg dat milestones en hun relaties geladen zijn
        if (!$this->relationLoaded('milestones')) {
            $this->load('milestones.tasks.subtasks');
        }
        
        // Bereken eerst de milestone uren (indien nog niet berekend)
        $totalHours = 0;
        foreach ($this->milestones as $milestone) {
            // Als milestone estimated_hours 0 is, bereken het uit taken
            if ($milestone->estimated_hours == 0) {
                $totalHours += $milestone->calculateEstimatedHours();
            } else {
                $totalHours += $milestone->estimated_hours;
            }
        }
        
        return round($totalHours, 2);
    }

    /**
     * Check if service can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Check if service has milestones
        if ($this->milestones()->count() > 0) {
            return false;
        }

        // Check if service is used in projects (when implemented)
        // if ($this->projectServices()->count() > 0) {
        //     return false;
        // }

        return true;
    }

    /**
     * Check if service can be archived
     */
    public function canBeArchived(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    /**
     * Archive service
     */
    public function archive(): bool
    {
        if (!$this->canBeArchived()) {
            return false;
        }

        return $this->update([
            'is_active' => false,
            'status' => 'inactive'
        ]);
    }

    /**
     * Activate service
     */
    public function activate(): bool
    {
        return $this->update([
            'is_active' => true,
            'status' => 'active'
        ]);
    }

/**
 * Get service usage statistics - ✅ COMPLETE WITH ALL KEYS
 */
public function getUsageStats(): array
{
    $totalMilestones = $this->total_milestones;
    $totalTasks = $this->total_tasks;
    $totalSubtasks = $this->total_subtasks;
    $totalHours = $this->total_structure_hours;
    
    return [
        // Main stats
        'projects_count' => 0, // TODO: implement when project integration exists
        'milestones_count' => $totalMilestones,
        'tasks_count' => $totalTasks,
        'subtasks_count' => $totalSubtasks,
        'total_hours' => $totalHours,
        'revenue_potential' => $this->total_price,
        
        // ✅ ALTERNATIVE KEY NAMES (for different views)
        'total_milestones' => $totalMilestones,
        'total_tasks' => $totalTasks,
        'total_subtasks' => $totalSubtasks,
        'total_estimated_hours' => $totalHours,  // ✅ ADDED: This was missing!
        'total_structure_hours' => $totalHours,
        
        // ✅ FINANCIAL STATS
        'total_price' => $this->total_price,
        'formatted_price' => $this->formatted_price,
        'calculated_hourly_rate' => $this->calculated_hourly_rate,
        
        // ✅ SERVICE INFO
        'service_name' => $this->name,
        'service_type' => $this->type_display,
        'is_active' => $this->is_active,
        'status' => $this->status,
    ];
}
    /**
     * Duplicate service with all structure
     */
    public function duplicate(string $newName = null): self
    {
        $newService = $this->replicate();
        $newService->name = $newName ?? ($this->name . ' (Copy)');
        $newService->sku_code = $this->sku_code ? ($this->sku_code . '-COPY') : null;
        $newService->save();

        // TODO: Duplicate milestones, tasks, subtasks
        // foreach ($this->milestones as $milestone) {
        //     $newMilestone = $milestone->duplicate($newService->id);
        // }

        return $newService;
    }
}