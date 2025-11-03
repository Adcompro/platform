<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
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
     * Service milestone behoort tot een service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Service milestone heeft taken
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ServiceTask::class)->orderBy('sort_order');
    }

    /**
     * Service milestone heeft subtaken (via taken)
     */
    public function subtasks()
    {
        return $this->hasManyThrough(ServiceSubtask::class, ServiceTask::class);
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope voor geordende milestones
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope voor milestones die inbegrepen zijn in prijs
     */
    public function scopeIncludedInPrice($query)
    {
        return $query->where('included_in_price', true);
    }

    // =====================================
    // COMPUTED ATTRIBUTES
    // =====================================

    /**
     * Get total estimated hours voor deze milestone (som van alle taken en subtaken)
     */
    public function getTotalEstimatedHoursAttribute(): float
    {
        // Zorg dat relaties geladen zijn
        if (!$this->relationLoaded('tasks')) {
            $this->load('tasks.subtasks');
        }
        
        $totalHours = 0;
        
        // Loop door alle taken
        foreach ($this->tasks as $task) {
            // Tel de uren van de taak zelf
            $totalHours += (float) ($task->estimated_hours ?? 0);
            
            // Zorg dat subtasks geladen zijn
            if (!$task->relationLoaded('subtasks')) {
                $task->load('subtasks');
            }
            
            // Tel de uren van alle subtaken
            foreach ($task->subtasks as $subtask) {
                $totalHours += (float) ($subtask->estimated_hours ?? 0);
            }
        }
        
        return round($totalHours, 2);
    }

    /**
     * Bereken de totale uren van taken en subtaken
     */
    public function calculateEstimatedHours(): float
    {
        return $this->getTotalEstimatedHoursAttribute();
    }

    /**
     * Get totaal aantal taken in deze milestone
     */
    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Get totaal aantal subtaken in deze milestone
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
        
        return 'bg-blue-100 text-blue-800';
    }

    // =====================================
    // BUSINESS LOGIC METHODS
    // =====================================

    /**
     * Bereken en update de totale estimated hours van alle taken en subtaken
     */
    public function calculateAndUpdateEstimatedHours(): void
    {
        $this->estimated_hours = $this->calculateEstimatedHours();
        $this->save();
    }

    /**
     * Check of milestone kan worden verwijderd
     */
    public function canBeDeleted(): bool
    {
        return $this->tasks()->count() === 0;
    }

    /**
     * Duplicate milestone met alle taken en subtaken
     */
    public function duplicate(int $newServiceId): self
    {
        $newMilestone = $this->replicate();
        $newMilestone->service_id = $newServiceId;
        $newMilestone->name = $this->name . ' (Copy)';
        $newMilestone->save();

        // Duplicate alle taken
        foreach ($this->tasks as $task) {
            $task->duplicate($newMilestone->id);
        }

        return $newMilestone;
    }

    /**
     * Update sort order en herorder andere milestones
     */
    public function updateSortOrder(int $newSortOrder): void
    {
        $oldSortOrder = $this->sort_order;
        
        if ($newSortOrder > $oldSortOrder) {
            // Verplaats naar boven - verlaag sort_order van items ertussen
            ServiceMilestone::where('service_id', $this->service_id)
                ->where('sort_order', '>', $oldSortOrder)
                ->where('sort_order', '<=', $newSortOrder)
                ->decrement('sort_order');
        } else {
            // Verplaats naar beneden - verhoog sort_order van items ertussen
            ServiceMilestone::where('service_id', $this->service_id)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $oldSortOrder)
                ->increment('sort_order');
        }
        
        $this->update(['sort_order' => $newSortOrder]);
    }
}