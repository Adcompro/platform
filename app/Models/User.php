<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\PasswordResetMail;
use App\Mail\UserVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'company_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        'role',
        'is_active',
        'auto_approve_time_entries',
        'last_login_at',
        'last_login_ip',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_approve_time_entries' => 'boolean',
        'password' => 'hashed',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Bedrijf waar deze gebruiker bij hoort
     */
    public function companyRelation(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Alias voor companyRelation (voor backwards compatibility)
     */
    public function company(): BelongsTo
    {
        return $this->companyRelation();
    }

    /**
     * Projecten waar deze gebruiker aan toegewezen is
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users')
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
     * Time entries van deze gebruiker
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Time entries die deze gebruiker heeft goedgekeurd
     */
    public function approvedTimeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'approved_by');
    }

    /**
     * Media monitors for this user
     */
    public function mediaMonitors(): HasMany
    {
        return $this->hasMany(UserMediaMonitor::class);
    }

    /**
     * Media mentions for this user
     */
    public function mediaMentions(): HasMany
    {
        return $this->hasMany(UserMediaMention::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope voor actieve gebruikers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope voor gebruikers van specifiek bedrijf
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope voor gebruikers met specifieke rol
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check of gebruiker super admin is
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check of gebruiker admin is (admin of super_admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check of gebruiker project manager is
     */
    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager';
    }

    /**
     * Check of gebruiker tijd mag goedkeuren
     */
    public function canApproveTime(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'project_manager']);
    }

    /**
     * Check of gebruiker financiële gegevens mag bekijken
     */
    public function canViewFinancials(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Haal volledige naam op
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        
        return $this->name;
    }

    /**
     * Haal rol badge class op voor UI
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'bg-purple-100 text-purple-800',
            'admin' => 'bg-red-100 text-red-800',
            'project_manager' => 'bg-blue-100 text-blue-800',
            'user' => 'bg-green-100 text-green-800',
            'reader' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Haal beschrijving van rol op
     */
    public function getRoleDescriptionAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'project_manager' => 'Project Manager',
            'user' => 'Regular User',
            'reader' => 'Read-only User',
            default => 'Unknown Role'
        };
    }

    /**
     * Send the password reset notification.
     * Gebruikt onze custom mail class voor password reset
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)->send(new PasswordResetMail($token, $this->email, $this->name));
    }

    /**
     * Send the email verification notification.
     * Gebruikt onze custom mail class voor email verificatie
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        Mail::to($this->email)->send(new UserVerificationMail($this, $verificationUrl));
    }
}