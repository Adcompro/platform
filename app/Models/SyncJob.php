<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncJob extends Model
{
    protected $fillable = [
        'job_type',
        'status',
        'user_id',
        'total_items',
        'processed_items',
        'successful_items',
        'failed_items',
        'current_item',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bereken progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_items === 0) {
            return 0;
        }
        return (int) round(($this->processed_items / $this->total_items) * 100);
    }

    /**
     * Check of sync nog bezig is
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Update progress
     */
    public function updateProgress(int $processed, int $successful, int $failed, ?string $currentItem = null): void
    {
        $this->update([
            'processed_items' => $processed,
            'successful_items' => $successful,
            'failed_items' => $failed,
            'current_item' => $currentItem,
        ]);
    }

    /**
     * Mark als compleet
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark als gefaald
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}
