<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserMsGraphToken extends Model
{
    protected $table = 'user_ms_graph_tokens';

    protected $fillable = [
        'user_id',
        'email',
        'access_token',
        'refresh_token',
        'expires_at',
        'account_info'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'account_info' => 'array'
    ];

    /**
     * Get token for current user
     */
    public static function getForCurrentUser()
    {
        if (!Auth::check()) {
            return null;
        }

        return static::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->first();
    }

    /**
     * Store token for current user
     */
    public static function storeForCurrentUser($tokenData, $accountInfo = null)
    {
        if (!Auth::check()) {
            return null;
        }

        // Delete any existing tokens for this user
        static::where('user_id', Auth::id())->delete();

        return static::create([
            'user_id' => Auth::id(),
            'email' => $accountInfo['email'] ?? null,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_at' => isset($tokenData['expires_in'])
                ? Carbon::now()->addSeconds($tokenData['expires_in'])
                : Carbon::now()->addHour(),
            'account_info' => $accountInfo
        ]);
    }

    /**
     * Check if token is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if token will expire soon (within 5 minutes)
     */
    public function willExpireSoon()
    {
        return $this->expires_at && $this->expires_at->subMinutes(5)->isPast();
    }

    /**
     * Get the user this token belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}