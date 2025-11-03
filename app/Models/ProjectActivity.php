<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ProjectActivity extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'activity_type',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship naar project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship naar gebruiker die actie uitvoerde
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper method om activity te loggen
     */
    public static function log(
        int $projectId,
        string $activityType,
        string $description,
        ?array $changedFields = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): void {
        $oldValues = [];
        $newValues = [];

        if ($changedFields) {
            foreach ($changedFields as $field => $values) {
                $oldValues[$field] = $values['old'] ?? null;
                $newValues[$field] = $values['new'] ?? null;
            }
        }

        self::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'activity_type' => $activityType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get badge class voor activity type
     */
    public function getActivityBadgeClassAttribute(): string
    {
        return match($this->activity_type) {
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'milestone_added' => 'bg-purple-100 text-purple-800',
            'milestone_updated' => 'bg-purple-100 text-purple-800',
            'milestone_deleted' => 'bg-red-100 text-red-800',
            'task_added' => 'bg-indigo-100 text-indigo-800',
            'task_updated' => 'bg-indigo-100 text-indigo-800',
            'task_deleted' => 'bg-red-100 text-red-800',
            'subtask_added' => 'bg-pink-100 text-pink-800',
            'subtask_updated' => 'bg-pink-100 text-pink-800',
            'subtask_deleted' => 'bg-red-100 text-red-800',
            'time_entry_added' => 'bg-yellow-100 text-yellow-800',
            'time_entry_approved' => 'bg-green-100 text-green-800',
            'time_entry_rejected' => 'bg-red-100 text-red-800',
            'status_changed' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get icon voor activity type
     */
    public function getActivityIconAttribute(): string
    {
        return match($this->activity_type) {
            'created' => 'fas fa-plus-circle',
            'updated' => 'fas fa-edit',
            'deleted' => 'fas fa-trash',
            'milestone_added', 'milestone_updated', 'milestone_deleted' => 'fas fa-flag',
            'task_added', 'task_updated', 'task_deleted' => 'fas fa-tasks',
            'subtask_added', 'subtask_updated', 'subtask_deleted' => 'fas fa-list',
            'time_entry_added' => 'fas fa-clock',
            'time_entry_approved' => 'fas fa-check-circle',
            'time_entry_rejected' => 'fas fa-times-circle',
            'status_changed' => 'fas fa-exchange-alt',
            default => 'fas fa-info-circle',
        };
    }

    /**
     * Format activity type voor display
     */
    public function getFormattedActivityTypeAttribute(): string
    {
        return match($this->activity_type) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'milestone_added' => 'Milestone Added',
            'milestone_updated' => 'Milestone Updated',
            'milestone_deleted' => 'Milestone Deleted',
            'task_added' => 'Task Added',
            'task_updated' => 'Task Updated',
            'task_deleted' => 'Task Deleted',
            'subtask_added' => 'Subtask Added',
            'subtask_updated' => 'Subtask Updated',
            'subtask_deleted' => 'Subtask Deleted',
            'time_entry_added' => 'Time Entry Added',
            'time_entry_approved' => 'Time Entry Approved',
            'time_entry_rejected' => 'Time Entry Rejected',
            'status_changed' => 'Status Changed',
            default => ucfirst(str_replace('_', ' ', $this->activity_type)),
        };
    }
}
