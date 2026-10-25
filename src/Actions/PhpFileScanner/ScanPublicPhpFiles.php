<?php

namespace WebduoNederland\BackboneAgent\Actions\PhpFileScanner;

use Illuminate\Pipeline\Pipeline;
use WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner\NotifyBackbonePipeline;
use WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner\ShutdownServerPipeline;

class ScanPublicPhpFiles
{
    public function scan(): void
    {
        $files = app(FindPublicPhpFiles::class)->find();

        if ($files === []) {
            return;
        }

        $pipelines = [
            NotifyBackbonePipeline::class,
        ];

        if (config()->boolean('backbone-agent.php_file_scanner.panic_mode', false)) {
            $pipelines[] = ShutdownServerPipeline::class;
        }

        app(Pipeline::class)
            ->send($files)
            ->through($pipelines)
            ->thenReturn();
    }
}
