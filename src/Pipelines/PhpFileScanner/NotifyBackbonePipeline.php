<?php

namespace WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner;

use Closure;
use Throwable;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;

class NotifyBackbonePipeline
{
    public function handle(array $files, Closure $next): mixed
    {
        try {
            app(BackboneApi::class)->http()
                ->connectTimeout(5)
                ->timeout(5)
                ->post('v1/php-file-scanner/detected', [
                    'files' => $files,
                ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $next($files);
    }
}
