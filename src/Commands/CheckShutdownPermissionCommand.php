<?php

namespace WebduoNederland\BackboneAgent\Commands;

use Exception;
use Illuminate\Console\Command;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\CheckShutdownPermission;

class CheckShutdownPermissionCommand extends Command
{
    protected $signature = 'backbone-agent:check-shutdown-permission';

    protected $description = 'Check if the server can be shut down in panic mode, without shutting it down';

    public function handle(CheckShutdownPermission $checkShutdownPermission): int
    {
        if (! config()->boolean('backbone-agent.php_file_scanner.panic_mode', false)) {
            $this->warn('Panic mode is disabled, the server will not be shut down when a PHP file is found.');
        }

        try {
            $allowed = $checkShutdownPermission->check();
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! $allowed) {
            $this->error('This user is not allowed to run the shutdown command!');

            return self::FAILURE;
        }

        $this->info('This user is allowed to run the shutdown command.');

        return self::SUCCESS;
    }
}
