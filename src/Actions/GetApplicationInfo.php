<?php

namespace WebduoNederland\BackboneAgent\Actions;

use Illuminate\Support\Facades\Artisan;

class GetApplicationInfo
{
    public function get(): array
    {
        $application = Artisan::getFacadeApplication();

        return [
            'laravel_version' => $application?->version(),
            'php_version' => phpversion(),
            'environment' => $application?->environment(),
            'debug_mode_enabled' => $application?->hasDebugModeEnabled(),
            'timezone' => config()->string('app.timezone'),
            'cache_driver' => config()->string('cache.default'),
            'queue_driver' => config()->string('queue.default'),
            'session_driver' => config()->string('session.driver'),
        ];
    }
}
