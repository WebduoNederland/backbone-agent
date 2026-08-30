<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions;

use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\GetApplicationInfo;
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

        $result = app(GetApplicationInfo::class)->get();

        $this->assertSame($this->app->version(), $result['laravel_version']);
        $this->assertSame(phpversion(), $result['php_version']);
        $this->assertSame($this->app->environment(), $result['environment']);
        $this->assertSame($this->app->hasDebugModeEnabled(), $result['debug_mode_enabled']);
        $this->assertSame('Europe/Amsterdam', $result['timezone']);
        $this->assertSame('database', $result['cache_driver']);
        $this->assertSame('sync', $result['queue_driver']);
        $this->assertSame('database', $result['session_driver']);
    }
}
