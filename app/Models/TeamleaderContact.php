<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamleaderContact extends Model
{
    protected $table = 'teamleader_contacts';

    protected $fillable = [
        'teamleader_id',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'mobile',
        'position',
        'language',
        'companies',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'city',
        'country',
        'linkedin',
        'twitter',
        'raw_data',
        'is_imported',
        'synced_at',
        'imported_at',
    ];

    protected $casts = [
        'companies' => 'json',
        'raw_data' => 'json',
        'is_imported' => 'boolean',
        'synced_at' => 'datetime',
        'imported_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if contact has company links
     */
    public function hasCompanies(): bool
    {
        return !empty($this->companies) && is_array($this->companies) && count($this->companies) > 0;
    }

    /**
     * Get first company name if available
     */
    public function getFirstCompanyName(): ?string
    {
        if ($this->hasCompanies() && isset($this->companies[0])) {
            return $this->companies[0];
        }
        return null;
    }
}
