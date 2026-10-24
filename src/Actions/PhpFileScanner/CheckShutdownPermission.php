<?php

namespace WebduoNederland\BackboneAgent\Actions\PhpFileScanner;

use Exception;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class CheckShutdownPermission
{
    public function check(): bool
    {
        $command = Str::of(config()->string('backbone-agent.php_file_scanner.shutdown_command'))
            ->squish()
            ->explode(' ');

        if ($command->first() !== 'sudo') {
            throw new Exception('The shutdown command does not use sudo, unable to check the permissions!');
        }

        $arguments = $command
            ->skip(1)
            ->skipWhile(fn (string $argument): bool => str_starts_with($argument, '-'))
            ->values()
            ->all();

        return Process::run(['sudo', '-n', '-l', ...$arguments])->successful();
    }
}
