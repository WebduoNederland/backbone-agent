<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\GetApplicationInfo;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\CheckShutdownPermission;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class GetApplicationInfoTest extends TestCase
{
    #[Test]
    public function it_can_get_application_info(): void
    {
        config()->set('app.timezone', 'Europe/Amsterdam');
        config()->set('cache.default', 'database');
        config()->set('queue.default', 'sync');
        config()->set('session.driver', 'database');
        config()->set('backbone-agent.php_file_scanner.panic_mode', true);

        $this->mock(CheckShutdownPermission::class)
            ->shouldReceive('check')
            ->once()
            ->andReturnTrue();

        $result = app(GetApplicationInfo::class)->get();

        $this->assertSame($this->app?->version(), $result['laravel_version']);
        $this->assertSame(phpversion(), $result['php_version']);
        $this->assertSame($this->app?->environment(), $result['environment']);
        $this->assertSame($this->app?->hasDebugModeEnabled(), $result['debug_mode_enabled']);
        $this->assertSame('Europe/Amsterdam', $result['timezone']);
        $this->assertSame('database', $result['cache_driver']);
        $this->assertSame('sync', $result['queue_driver']);
        $this->assertSame('database', $result['session_driver']);
        $this->assertTrue($result['php_file_scanner_panic_mode_enabled']);
        $this->assertTrue($result['php_file_scanner_shutdown_permission']);
    }

    #[Test]
    public function it_returns_no_shutdown_permission_when_it_cannot_be_checked(): void
    {
        $this->mock(CheckShutdownPermission::class)
            ->shouldReceive('check')
            ->once()
            ->andThrow(new Exception('::exception-message::'));

        $result = app(GetApplicationInfo::class)->get();

        $this->assertFalse($result['php_file_scanner_panic_mode_enabled']);
        $this->assertNull($result['php_file_scanner_shutdown_permission']);
    }
}
