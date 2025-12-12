<?php

namespace App\Listeners;

use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $request = request();

        UserLoginLog::logLogin(
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        // Update last_login_at on user
        $user->update(['last_login_at' => now()]);
    }
}
