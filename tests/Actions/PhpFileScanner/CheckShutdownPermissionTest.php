<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions\PhpFileScanner;

use Exception;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\CheckShutdownPermission;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class CheckShutdownPermissionTest extends TestCase
{
    #[Test]
    public function it_returns_true_when_the_shutdown_command_is_allowed(): void
    {
        Process::fake();

        config()->set('backbone-agent.php_file_scanner.shutdown_command', 'sudo -n /usr/sbin/shutdown -h now');

        $this->assertTrue(app(CheckShutdownPermission::class)->check());

        Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['sudo', '-n', '-l', '/usr/sbin/shutdown', '-h', 'now']);
    }

    #[Test]
    public function it_returns_false_when_the_shutdown_command_is_not_allowed(): void
    {
        Process::fake([
            '*' => Process::result(errorOutput: 'sudo: a password is required', exitCode: 1),
        ]);

        $this->assertFalse(app(CheckShutdownPermission::class)->check());

        Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['sudo', '-n', '-l', 'shutdown', '-h', 'now']);
    }

    #[Test]
    public function it_throws_an_exception_when_the_shutdown_command_does_not_use_sudo(): void
    {
        Process::fake();

        config()->set('backbone-agent.php_file_scanner.shutdown_command', '/usr/sbin/shutdown -h now');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The shutdown command does not use sudo, unable to check the permissions!');

        app(CheckShutdownPermission::class)->check();
    }
}
