<?php

namespace WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner;

use Closure;
use Exception;
use Illuminate\Support\Facades\Process;

class ShutdownServerPipeline
{
    public function handle(array $files, Closure $next): mixed
    {
        $result = Process::run(config()->string('backbone-agent.php_file_scanner.shutdown_command'));

        if ($result->failed()) {
            report(new Exception('Unable to shut down the server: '.trim($result->errorOutput())));
        }

        return $next($files);
    }
}
