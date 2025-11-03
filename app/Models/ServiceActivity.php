<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'user_id',
        'action',
        'description',
        'changes',
        'ip_address'
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get icon for activity type
     */
    public function getIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'fas fa-plus-circle text-green-500',
            'updated' => 'fas fa-edit text-blue-500',
            'deleted' => 'fas fa-trash text-red-500',
            'restored' => 'fas fa-undo text-green-500',
            'activated' => 'fas fa-check-circle text-green-500',
            'deactivated' => 'fas fa-times-circle text-red-500',
            'structure_added' => 'fas fa-sitemap text-purple-500',
            'structure_updated' => 'fas fa-project-diagram text-blue-500',
            'structure_removed' => 'fas fa-unlink text-red-500',
            'price_updated' => 'fas fa-euro-sign text-yellow-500',
            'imported' => 'fas fa-file-import text-indigo-500',
            'exported' => 'fas fa-file-export text-teal-500',
            default => 'fas fa-info-circle text-gray-500'
        };
    }

    /**
     * Get badge color for activity type
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'restored' => 'bg-green-100 text-green-800',
            'activated' => 'bg-green-100 text-green-800',
            'deactivated' => 'bg-red-100 text-red-800',
            'structure_added' => 'bg-purple-100 text-purple-800',
            'structure_updated' => 'bg-blue-100 text-blue-800',
            'structure_removed' => 'bg-red-100 text-red-800',
            'price_updated' => 'bg-yellow-100 text-yellow-800',
            'imported' => 'bg-indigo-100 text-indigo-800',
            'exported' => 'bg-teal-100 text-teal-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Format changes for display
     */
    public function getFormattedChangesAttribute(): array
    {
        if (!$this->changes) {
            return [];
        }

        $formatted = [];
        foreach ($this->changes as $field => $values) {
            // Skip certain fields from display
            if (in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }

            // Field is already formatted from controller
            $displayField = $field;
            
            // Only format if it's still in snake_case
            if (strpos($field, '_') !== false) {
                $displayField = ucfirst(str_replace('_', ' ', $field));
            }

            $formatted[] = [
                'field' => $displayField,
                'old' => $values['old'] ?? null,
                'new' => $values['new'] ?? null
            ];
        }

        return $formatted;
    }

    /**
     * Create activity log entry
     */
    public static function log($serviceId, $action, $description, $changes = null)
    {
        return self::create([
            'service_id' => $serviceId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip()
        ]);
    }
}