<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamleaderCompany extends Model
{
    protected $fillable = [
        'teamleader_id',
        'name',
        'vat_number',
        'emails',
        'website',
        'line_1',
        'line_2',
        'postal_code',
        'city',
        'country',
        'status',
        'raw_data',
        'is_imported',
        'imported_at',
        'synced_at',
    ];

    protected $casts = [
        'is_imported' => 'boolean',
        'raw_data' => 'json',
        'imported_at' => 'datetime',
        'synced_at' => 'datetime',
    ];
}
