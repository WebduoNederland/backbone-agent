<?php

namespace WebduoNederland\BackboneAgent\Commands;

use Illuminate\Console\Command;
use WebduoNederland\BackboneAgent\Jobs\PhpFileScanner\ScanPublicPhpFilesJob;

class ScanPublicPhpFilesCommand extends Command
{
    protected $signature = 'backbone-agent:scan-public-php-files';

    protected $description = 'Scan the public folder for injected PHP files';

    public function handle(): int
    {
        ScanPublicPhpFilesJob::dispatch();

        return self::SUCCESS;
    }
}
