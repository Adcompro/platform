<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDraftAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'action',
        'details',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'details' => 'array',
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'line_added' => 'Line Added',
            'line_removed' => 'Line Removed',
            'line_merged' => 'Lines Merged',
            'description_changed' => 'Description Changed',
            'amount_adjusted' => 'Amount Adjusted',
            'finalized' => 'Finalized',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'blue',
            'line_added' => 'green',
            'line_removed' => 'red',
            'line_merged' => 'yellow',
            'description_changed' => 'blue',
            'amount_adjusted' => 'orange',
            'finalized' => 'purple',
            default => 'gray'
        };
    }
}