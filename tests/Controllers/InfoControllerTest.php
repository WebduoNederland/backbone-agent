<?php

namespace WebduoNederland\BackboneAgent\Tests\Controllers;

use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\GetApplicationInfo;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class InfoControllerTest extends TestCase
{
    #[Test]
    public function it_returns_the_application_information(): void
    {
        config()->set('backbone-agent.api_key', '::api-key::');

        $expected = [
            'laravel_version' => '13.0.0',
            'php_version' => '8.5.0',
            'environment' => 'testing',
            'debug_mode_enabled' => false,
            'timezone' => 'Europe/Amsterdam',
            'cache_driver' => 'database',
            'queue_driver' => 'sync',
            'session_driver' => 'database',
        ];

        $this->mock(GetApplicationInfo::class)
            ->shouldReceive('get')
            ->once()
            ->andReturn($expected);

        $response = $this->withHeader('Authorization', 'Bearer ::api-key::')
            ->getJson('api/backbone/v1/info');

        $response->assertStatus(200)
            ->assertExactJson([
                'success' => true,
                'message' => '',
                'data' => $expected,
            ]);
    }
}
