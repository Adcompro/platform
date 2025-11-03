<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginActivity extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'plugin_id',
        'user_id',
        'action',
        'old_settings',
        'new_settings',
        'ip_address',
        'notes',
    ];

    protected $casts = [
        'old_settings' => 'array',
        'new_settings' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // Relationships
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get action badge
    public function getActionBadgeAttribute(): string
    {
        $colors = [
            'activated' => 'green',
            'deactivated' => 'red',
            'configured' => 'blue',
            'installed' => 'indigo',
            'uninstalled' => 'gray',
        ];

        $color = $colors[$this->action] ?? 'gray';

        return sprintf(
            '<span class="px-2 py-1 text-xs font-medium bg-%s-100 text-%s-800 rounded-full">%s</span>',
            $color,
            $color,
            ucfirst($this->action)
        );
    }

    // Get formatted description
    public function getDescriptionAttribute(): string
    {
        $descriptions = [
            'activated' => 'activated the plugin',
            'deactivated' => 'deactivated the plugin',
            'configured' => 'updated plugin configuration',
            'installed' => 'installed the plugin',
            'uninstalled' => 'uninstalled the plugin',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }
}