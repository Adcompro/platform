<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
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
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
            'status_changed' => 'fas fa-toggle-on text-yellow-500',
            'activated' => 'fas fa-check-circle text-green-500',
            'deactivated' => 'fas fa-times-circle text-red-500',
            'company_added' => 'fas fa-building text-purple-500',
            'company_removed' => 'fas fa-building text-red-500',
            'address_updated' => 'fas fa-map-marker-alt text-indigo-500',
            'contact_added' => 'fas fa-user-plus text-blue-500',
            'contact_removed' => 'fas fa-user-minus text-red-500',
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
            'status_changed' => 'bg-yellow-100 text-yellow-800',
            'activated' => 'bg-green-100 text-green-800',
            'deactivated' => 'bg-red-100 text-red-800',
            'company_added' => 'bg-purple-100 text-purple-800',
            'company_removed' => 'bg-pink-100 text-pink-800',
            'address_updated' => 'bg-indigo-100 text-indigo-800',
            'contact_added' => 'bg-blue-100 text-blue-800',
            'contact_removed' => 'bg-red-100 text-red-800',
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

            // Use the field as-is if it's already formatted (from controller)
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
    public static function log($customerId, $action, $description, $changes = null)
    {
        return self::create([
            'customer_id' => $customerId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip()
        ]);
    }
}