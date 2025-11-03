<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class UpdateLastLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        
        // Update last login timestamp and IP (with safety check)
        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => Request::ip()
            ]);
        } catch (\Exception $e) {
            // If columns don't exist, just log the login without tracking
            // This prevents login failures due to missing columns
            \Log::warning('Could not update last login info: ' . $e->getMessage());
        }
    }
}