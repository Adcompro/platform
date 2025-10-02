<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasStatusManagement;

class Project extends Model
{
    use HasFactory, HasStatusManagement;

    protected $fillable = [
        'company_id',
        'customer_id',
        'template_id',
        'invoice_template_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'monthly_fee',
        'fee_start_date',
        'fee_rollover_enabled',
        'default_hourly_rate',
        'main_invoicing_company_id',
        'vat_rate',
        'billing_frequency',
        'billing_interval_days',
        'next_billing_date',
        'last_billing_date',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fee_start_date' => 'date',
        'next_billing_date' => 'date',
        'last_billing_date' => 'date',
        'monthly_fee' => 'decimal:2',
        'default_hourly_rate' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'fee_rollover_enabled' => 'boolean',
        'billing_interval_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Bedrijf waar dit project onder valt (voor multi-tenant isolatie)
     */
    public function companyRelation(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Klant voor wie dit project wordt uitgevoerd
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Invoice template preference for this project
     */
    public function invoiceTemplate(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    /**
     * Template waarvan dit project is gemaakt
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }

    /**
     * Hoofdfacturerende BV voor dit project
     */
    public function mainInvoicingCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'main_invoicing_company_id');
    }

    /**
     * Gebruiker die project aanmaakte
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Gebruiker die project laatst wijzigde
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Gebruiker die project verwijderde
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Bedrijven die aan dit project werken (multi-BV met doorbelasting)
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'project_companies')
                    ->withPivot([
                        'role',
                        'billing_method',
                        'hourly_rate_override',
                        'monthly_fixed_amount',
                        'billing_start_date',
                        'billing_end_date',
                        'is_active',
                        'notes'
                    ])
                    ->withTimestamps();
    }

    /**
     * Gebruikers toegewezen aan dit project (projectteam)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
                    ->withPivot([
                        'role_override',
                        'can_edit_fee',
                        'can_view_financials',
                        'can_log_time',
                        'can_approve_time',
                        'added_by',
                        'added_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Mijlpalen van dit project
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order');
    }

    /**
     * Tijd registraties voor dit project
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Maandelijkse fees van dit project
     */
    public function monthlyFees(): HasMany
    {
        return $this->hasMany(ProjectMonthlyFee::class);
    }

    /**
     * Extra kosten van dit project
     */
    public function additionalCosts(): HasMany
    {
        return $this->hasMany(ProjectAdditionalCost::class);
    }

    /**
     * Maandelijkse extra kosten van dit project
     */
    public function monthlyAdditionalCosts(): HasMany
    {
        return $this->hasMany(ProjectMonthlyAdditionalCost::class);
    }

    /**
     * Facturen voor dit project
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Intercompany doorbelastingen voor dit project
     */
    public function intercompanyCharges(): HasMany
    {
        return $this->hasMany(MonthlyIntercompanyCharge::class);
    }

    /**
     * AI settings voor dit project
     */
    public function aiSettings(): HasOne
    {
        return $this->hasOne(ProjectAiSetting::class);
    }
    
    /**
     * Get AI settings or create default
     */
    public function getAiSettings()
    {
        if (!$this->aiSettings) {
            return new ProjectAiSetting([
                'project_id' => $this->id,
                'use_global_settings' => true
            ]);
        }
        
        return $this->aiSettings;
    }

    /**
     * Media campaigns for this project
     */
    public function mediaCampaigns(): HasMany
    {
        return $this->hasMany(ProjectMediaCampaign::class);
    }

    /**
     * Media mentions linked to this project
     */
    public function mediaMentions(): HasMany
    {
        return $this->hasMany(ProjectMediaMention::class);
    }

    // ========================================
    // SCOPES - Voor herbruikbare queries
    // ========================================

    /**
     * Alleen actieve projecten
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'active']);
    }

    /**
     * Alleen afgeronde projecten
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Projecten voor specifiek bedrijf (company isolation)
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Projecten voor specifieke klant
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Projecten met maandelijkse fee
     */
    public function scopeWithMonthlyFee($query)
    {
        return $query->whereNotNull('monthly_fee')->where('monthly_fee', '>', 0);
    }

    // ========================================
    // HELPER METHODS - Business Logic
    // ========================================

    /**
     * Check of project kan worden verwijderd
     */
    public function canBeDeleted(): bool
    {
        // Check of er tijd geregistreerd is
        if ($this->timeEntries()->exists()) {
            return false;
        }

        // Check of er facturen zijn
        if ($this->invoices()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Haal projectmanagers op
     */
    public function getProjectManagersAttribute()
    {
        return $this->users()
            ->where(function($query) {
                $query->where('users.role', 'project_manager')
                      ->orWhere('project_users.role_override', 'project_manager');
            })->get();
    }

    /**
     * Bereken totale project fee/budget
     */
    public function getTotalProjectFeeAttribute(): float
    {
        $milestonesFee = $this->milestones()
            ->where('fee_type', 'in_fee')
            ->sum('fixed_price');
            
        $additionalFee = $this->milestones()
            ->where('fee_type', 'extended')
            ->sum('fixed_price');
            
        return $milestonesFee + $additionalFee;
    }

    /**
     * Bereken project voortgang percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalMilestones = $this->milestones()->count();
        
        if ($totalMilestones === 0) {
            return 0;
        }
        
        $completedMilestones = $this->milestones()
            ->where('status', 'completed')
            ->count();
            
        return round(($completedMilestones / $totalMilestones) * 100, 1);
    }

    /**
     * Haal totale gewerkte uren op
     */
    public function getTotalHoursWorkedAttribute(): float
    {
        return $this->timeEntries()
            ->where('status', 'approved')
            ->sum('hours');
    }

    /**
     * Check of project maandelijkse fee heeft
     */
    public function getHasMonthlyFeeAttribute(): bool
    {
        return !is_null($this->monthly_fee) && $this->monthly_fee > 0;
    }

    /**
     * Get status badge CSS class voor views
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'active' => 'bg-green-100 text-green-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'on_hold' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
    
    /**
     * Calculate the next billing date based on billing frequency
     */
    public function calculateNextBillingDate()
    {
        // Als er al een next_billing_date handmatig is ingesteld, gebruik die
        if ($this->next_billing_date && $this->next_billing_date->isFuture()) {
            return $this->next_billing_date;
        }
        
        // Get invoice settings
        $invoiceMonthlyDay = \App\Models\Setting::get('invoice_monthly_day', 'last');
        $invoiceQuarterlyTiming = \App\Models\Setting::get('invoice_quarterly_timing', 'quarter_end');
        $invoiceMilestoneDays = (int) \App\Models\Setting::get('invoice_milestone_days', 0);
        $invoiceProjectCompletionDays = (int) \App\Models\Setting::get('invoice_project_completion_days', 0);
        
        $baseDate = $this->last_billing_date 
            ? $this->last_billing_date 
            : ($this->fee_start_date ?? $this->start_date ?? now());
        
        switch ($this->billing_frequency) {
            case 'monthly':
                // Bepaal volgende factuur datum op basis van settings
                $nextMonth = $baseDate->copy()->addMonth();
                
                if ($invoiceMonthlyDay === 'last') {
                    // Laatste dag van de maand
                    return $nextMonth->endOfMonth();
                } elseif ($invoiceMonthlyDay === 'first_next') {
                    // 1e van de volgende maand
                    return $nextMonth->addMonth()->startOfMonth();
                } else {
                    // Specifieke dag van de maand
                    $day = min((int)$invoiceMonthlyDay, $nextMonth->daysInMonth);
                    return $nextMonth->day($day);
                }
                
            case 'quarterly':
                // Bepaal volgende kwartaal datum
                $nextQuarter = $baseDate->copy()->addQuarter();
                
                if ($invoiceQuarterlyTiming === 'quarter_end') {
                    // Einde van het kwartaal
                    return $nextQuarter->lastOfQuarter();
                } elseif ($invoiceQuarterlyTiming === 'quarter_start') {
                    // Begin van volgende kwartaal
                    return $nextQuarter->firstOfQuarter()->addQuarter();
                } else { // quarter_after_15
                    // 15e van de maand na het kwartaal
                    return $nextQuarter->lastOfQuarter()->addDays(15);
                }
                
            case 'milestone':
                // Check voor volgende completed milestone die nog niet gefactureerd is
                $nextMilestone = $this->milestones()
                    ->where('status', 'completed')
                    ->whereDoesntHave('invoices')
                    ->orderBy('end_date')
                    ->first();
                    
                if ($nextMilestone && $nextMilestone->end_date) {
                    // Voeg dagen toe op basis van settings
                    return $nextMilestone->end_date->copy()->addDays($invoiceMilestoneDays);
                }
                return null;
                
            case 'project_completion':
                // Alleen factureren bij project completion
                if ($this->status === 'completed' && $this->end_date) {
                    // Voeg dagen toe op basis van settings
                    return $this->end_date->copy()->addDays($invoiceProjectCompletionDays);
                }
                return null;
                
            case 'custom':
                // Custom interval in dagen
                if ($this->billing_interval_days) {
                    return $baseDate->copy()->addDays($this->billing_interval_days);
                }
                return null;
                
            default:
                return null;
        }
    }
    
    /**
     * Update the next billing date and save to database
     */
    public function updateNextBillingDate()
    {
        $nextDate = $this->calculateNextBillingDate();
        
        if ($nextDate) {
            $this->next_billing_date = $nextDate;
            $this->save();
        }
        
        return $nextDate;
    }
    
    /**
     * Check if project is ready for invoicing
     */
    public function isReadyForInvoicing(): bool
    {
        // Project moet actief zijn
        if (!in_array($this->status, ['active', 'completed'])) {
            return false;
        }
        
        // Check of er een volgende factuur datum is
        $nextBillingDate = $this->calculateNextBillingDate();
        
        if (!$nextBillingDate) {
            return false;
        }
        
        // Check of de datum verstreken is of vandaag is
        return $nextBillingDate->isToday() || $nextBillingDate->isPast();
    }
    
    /**
     * Get dagen tot volgende factuur
     */
    public function getDaysUntilNextBillingAttribute(): ?int
    {
        $nextDate = $this->calculateNextBillingDate();
        
        if (!$nextDate) {
            return null;
        }
        
        if ($nextDate->isPast()) {
            return 0; // Klaar voor facturatie
        }
        
        return now()->diffInDays($nextDate);
    }
    
    /**
     * Get all missed invoice periods
     * Returns an array of periods that should have been invoiced but weren't
     */
    public function getMissedInvoicePeriods()
    {
        $missedPeriods = [];
        
        // Bepaal start datum voor controle
        $checkStartDate = $this->fee_start_date ?? $this->start_date;
        if (!$checkStartDate) {
            return $missedPeriods;
        }
        
        // Project moet actief of completed zijn
        if (!in_array($this->status, ['active', 'completed'])) {
            return $missedPeriods;
        }
        
        // Get invoice settings
        $invoiceMonthlyDay = \App\Models\Setting::get('invoice_monthly_day', 'last');
        
        // Loop door alle periodes vanaf start tot nu
        $currentDate = $checkStartDate->copy();
        $today = now();
        
        while ($currentDate->lessThan($today)) {
            $periodStart = $currentDate->copy()->startOfMonth();
            $periodEnd = $currentDate->copy()->endOfMonth();
            
            // Check of er een factuur bestaat voor deze periode
            $invoiceExists = $this->invoices()
                ->where(function($query) use ($periodStart, $periodEnd) {
                    $query->whereBetween('period_start', [$periodStart, $periodEnd])
                        ->orWhereBetween('period_end', [$periodStart, $periodEnd]);
                })
                ->exists();
            
            // Als er geen factuur is en de periode is verstreken
            if (!$invoiceExists) {
                // Bepaal de invoice datum voor deze periode
                $invoiceDate = $this->calculateInvoiceDateForPeriod($periodStart, $periodEnd);
                
                if ($invoiceDate && $invoiceDate->isPast()) {
                    $missedPeriods[] = [
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'invoice_date' => $invoiceDate,
                        'month_label' => $periodStart->format('F Y'),
                        'days_overdue' => $invoiceDate->diffInDays(now())
                    ];
                }
            }
            
            // Ga naar volgende periode op basis van billing frequency
            switch ($this->billing_frequency) {
                case 'monthly':
                    $currentDate->addMonth();
                    break;
                case 'quarterly':
                    $currentDate->addQuarter();
                    break;
                case 'custom':
                    if ($this->billing_interval_days) {
                        $currentDate->addDays($this->billing_interval_days);
                    } else {
                        break 2; // Exit the while loop
                    }
                    break;
                default:
                    break 2; // Exit the while loop for milestone/project_completion
            }
        }
        
        return $missedPeriods;
    }
    
    /**
     * Calculate invoice date for a specific period
     */
    private function calculateInvoiceDateForPeriod($periodStart, $periodEnd)
    {
        $invoiceMonthlyDay = \App\Models\Setting::get('invoice_monthly_day', 'last');
        
        switch ($this->billing_frequency) {
            case 'monthly':
                if ($invoiceMonthlyDay === 'last') {
                    return $periodEnd;
                } elseif ($invoiceMonthlyDay === 'first_next') {
                    return $periodEnd->copy()->addDay();
                } else {
                    $day = min((int)$invoiceMonthlyDay, $periodEnd->day);
                    return $periodStart->copy()->day($day);
                }
            case 'quarterly':
                return $periodEnd;
            default:
                return $periodEnd;
        }
    }
    
    /**
     * Check if project has missed invoices
     */
    public function hasMissedInvoices(): bool
    {
        return count($this->getMissedInvoicePeriods()) > 0;
    }
    
    /**
     * Get count of missed invoices
     */
    public function getMissedInvoiceCountAttribute(): int
    {
        return count($this->getMissedInvoicePeriods());
    }

    // ========================================
    // BOOT METHOD - Voor automatische audit fields
    // ========================================

    protected static function boot()
    {
        parent::boot();

        // Automatisch created_by instellen bij aanmaken
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        // Automatisch updated_by instellen bij wijzigen
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // Automatisch deleted_by instellen bij soft delete
        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}