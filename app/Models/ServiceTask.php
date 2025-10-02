<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_milestone_id',
        'name',
        'description',
        'sort_order',
        'estimated_hours',
        'included_in_price',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'included_in_price' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    /**
     * Service task behoort tot een milestone
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ServiceMilestone::class, 'service_milestone_id');
    }

    /**
     * Service task behoort tot een service (via milestone)
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Service task heeft subtaken
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(ServiceSubtask::class)->orderBy('sort_order');
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope voor geordende taken
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope voor taken die inbegrepen zijn in prijs
     */
    public function scopeIncludedInPrice($query)
    {
        return $query->where('included_in_price', true);
    }

    // =====================================
    // COMPUTED ATTRIBUTES
    // =====================================

    /**
     * Get total estimated hours voor deze task (inclusief subtaken)
     */
    public function getTotalEstimatedHoursAttribute(): float
    {
        $taskHours = $this->estimated_hours ?? 0;
        $subtaskHours = $this->subtasks->sum('estimated_hours');
        
        return $taskHours + $subtaskHours;
    }

    /**
     * Get totaal aantal subtaken in deze task
     */
    public function getTotalSubtasksAttribute(): int
    {
        return $this->subtasks()->count();
    }

    /**
     * Get status badge class voor UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->included_in_price) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-yellow-100 text-yellow-800';
    }

    // =====================================
    // BUSINESS LOGIC METHODS
    // =====================================

    /**
     * Check of task kan worden verwijderd
     */
    public function canBeDeleted(): bool
    {
        return $this->subtasks()->count() === 0;
    }

    /**
     * Duplicate task met alle subtaken
     */
    public function duplicate(int $newMilestoneId): self
    {
        $newTask = $this->replicate();
        $newTask->service_milestone_id = $newMilestoneId;
        $newTask->name = $this->name . ' (Copy)';
        $newTask->save();

        // Duplicate alle subtaken
        foreach ($this->subtasks as $subtask) {
            $subtask->duplicate($newTask->id);
        }

        return $newTask;
    }

    /**
     * Update sort order en herorder andere taken
     */
    public function updateSortOrder(int $newSortOrder): void
    {
        $oldSortOrder = $this->sort_order;
        
        if ($newSortOrder > $oldSortOrder) {
            ServiceTask::where('service_milestone_id', $this->service_milestone_id)
                ->where('sort_order', '>', $oldSortOrder)
                ->where('sort_order', '<=', $newSortOrder)
                ->decrement('sort_order');
        } else {
            ServiceTask::where('service_milestone_id', $this->service_milestone_id)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $oldSortOrder)
                ->increment('sort_order');
        }
        
        $this->update(['sort_order' => $newSortOrder]);
    }

    /**
     * Calculate and update the estimated hours from subtasks
     */
    public function calculateAndUpdateEstimatedHours(): void
    {
        // Calculate total hours from subtasks
        $totalHours = $this->estimated_hours ?? 0;
        
        // Add hours from all subtasks
        foreach ($this->subtasks as $subtask) {
            $totalHours += $subtask->estimated_hours ?? 0;
        }
        
        // For now we don't update the task's own hours since it has its own value
        // The milestone will sum up both task hours and subtask hours
        
        // But we do need to trigger the milestone recalculation
        if ($this->milestone) {
            $this->milestone->calculateAndUpdateEstimatedHours();
        }
    }
}