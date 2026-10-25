<?php

namespace WebduoNederland\BackboneAgent\Tests\Commands;

use Exception;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\CheckShutdownPermission;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class CheckShutdownPermissionCommandTest extends TestCase
{
    #[Test]
    public function it_succeeds_when_the_shutdown_command_is_allowed(): void
    {
        config()->set('backbone-agent.php_file_scanner.panic_mode', true);

        $this->mock(CheckShutdownPermission::class)
            ->shouldReceive('check')
            ->once()
            ->andReturnTrue();

        $this->assertSame(0, Artisan::call('backbone-agent:check-shutdown-permission'));

        $output = Artisan::output();

        $this->assertStringContainsString('This user is allowed to run the shutdown command.', $output);
        $this->assertStringNotContainsString('Panic mode is disabled', $output);
    }

    #[Test]
    public function it_fails_when_the_shutdown_command_is_not_allowed(): void
    {
        $this->mock(CheckShutdownPermission::class)
            ->shouldReceive('check')
            ->once()
            ->andReturnFalse();

        $this->assertSame(1, Artisan::call('backbone-agent:check-shutdown-permission'));

        $output = Artisan::output();

        $this->assertStringContainsString('Panic mode is disabled, the server will not be shut down when a PHP file is found.', $output);
        $this->assertStringContainsString('This user is not allowed to run the shutdown command!', $output);
    }

    #[Test]
    public function it_fails_when_the_permissions_cannot_be_checked(): void
    {
        $this->mock(CheckShutdownPermission::class)
            ->shouldReceive('check')
            ->once()
            ->andThrow(new Exception('::exception-message::'));

        $this->assertSame(1, Artisan::call('backbone-agent:check-shutdown-permission'));

        $this->assertStringContainsString('::exception-message::', Artisan::output());
    }
}
