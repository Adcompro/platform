<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_task_id',
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
     * Service subtask behoort tot een task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ServiceTask::class, 'service_task_id');
    }

    /**
     * Service subtask behoort tot een milestone (via task)
     */
    public function milestone(): BelongsTo
    {
        return $this->task->milestone();
    }

    /**
     * Service subtask behoort tot een service (via task en milestone)
     */
    public function service(): BelongsTo
    {
        return $this->task->milestone->service();
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope voor geordende subtaken
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope voor subtaken die inbegrepen zijn in prijs
     */
    public function scopeIncludedInPrice($query)
    {
        return $query->where('included_in_price', true);
    }

    // =====================================
    // COMPUTED ATTRIBUTES
    // =====================================

    /**
     * Get status badge class voor UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->included_in_price) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-gray-100 text-gray-800';
    }

    // =====================================
    // BUSINESS LOGIC METHODS
    // =====================================

    /**
     * Check of subtask altijd kan worden verwijderd (heeft geen children)
     */
    public function canBeDeleted(): bool
    {
        return true; // Subtasks hebben geen children
    }

    /**
     * Duplicate subtask
     */
    public function duplicate(int $newTaskId): self
    {
        $newSubtask = $this->replicate();
        $newSubtask->service_task_id = $newTaskId;
        $newSubtask->name = $this->name . ' (Copy)';
        $newSubtask->save();

        return $newSubtask;
    }

    /**
     * Update sort order en herorder andere subtaken
     */
    public function updateSortOrder(int $newSortOrder): void
    {
        $oldSortOrder = $this->sort_order;
        
        if ($newSortOrder > $oldSortOrder) {
            ServiceSubtask::where('service_task_id', $this->service_task_id)
                ->where('sort_order', '>', $oldSortOrder)
                ->where('sort_order', '<=', $newSortOrder)
                ->decrement('sort_order');
        } else {
            ServiceSubtask::where('service_task_id', $this->service_task_id)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $oldSortOrder)
                ->increment('sort_order');
        }
        
        $this->update(['sort_order' => $newSortOrder]);
    }
}