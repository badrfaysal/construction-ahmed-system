<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            \App\Models\SystemActivityLog::create([
                'batch_id'   => (string) \Illuminate\Support\Str::uuid(),
                'user_id'    => $event->user->id,
                'ip_address' => request()->ip(),
                'action'     => 'login',
                'model_type' => get_class($event->user),
                'model_id'   => $event->user->id,
                'old_values' => null,
                'new_values' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log login activity: ' . $e->getMessage());
        }
    }
}
