<?php

namespace WebduoNederland\BackboneAgent\Jobs\PhpFileScanner;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\ScanPublicPhpFiles;

class ScanPublicPhpFilesJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(config()->string('backbone-agent.php_file_scanner.queue'));
    }

    public function handle(ScanPublicPhpFiles $scanPublicPhpFiles): void
    {
        $scanPublicPhpFiles->scan();
    }

    public function tries(): int
    {
        return 1;
    }
}
