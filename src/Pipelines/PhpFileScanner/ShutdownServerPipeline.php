<?php

namespace WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner;

use Closure;
use Exception;
use Illuminate\Support\Facades\Process;
use Throwable;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;

class ShutdownServerPipeline
{
    public function handle(array $files, Closure $next): mixed
    {
        if ($this->panicModeIsPaused()) {
            return $next($files);
        }

        $result = Process::run(config()->string('backbone-agent.php_file_scanner.shutdown_command'));

        if ($result->failed()) {
            report(new Exception('Unable to shut down the server: '.trim($result->errorOutput())));
        }

        return $next($files);
    }

    protected function panicModeIsPaused(): bool
    {
        try {
            return app(BackboneApi::class)->http()
                ->connectTimeout(5)
                ->timeout(5)
                ->get('v1/php-file-scanner/panic-mode')
                ->throw()
                ->json('paused') === true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
