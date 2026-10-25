<?php

namespace WebduoNederland\BackboneAgent\Actions;

use Exception;
use Illuminate\Support\Facades\Artisan;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\CheckShutdownPermission;

class GetApplicationInfo
{
    public function get(): array
    {
        $application = Artisan::getFacadeApplication();

        try {
            $phpFileScannerShutdownPermission = app(CheckShutdownPermission::class)->check();
        } catch (Exception) {
            $phpFileScannerShutdownPermission = null;
        }

        return [
            'laravel_version' => $application?->version(),
            'php_version' => phpversion(),
            'environment' => $application?->environment(),
            'debug_mode_enabled' => $application?->hasDebugModeEnabled(),
            'timezone' => config()->string('app.timezone'),
            'cache_driver' => config()->string('cache.default'),
            'queue_driver' => config()->string('queue.default'),
            'session_driver' => config()->string('session.driver'),
            'php_file_scanner_panic_mode_enabled' => config()->boolean('backbone-agent.php_file_scanner.panic_mode', false),
            'php_file_scanner_shutdown_permission' => $phpFileScannerShutdownPermission,
        ];
    }
}
