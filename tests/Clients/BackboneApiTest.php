<?php

namespace WebduoNederland\BackboneAgent\Tests\Clients;

use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class BackboneApiTest extends TestCase
{
    #[Test]
    public function it_builds_a_correct_request(): void
    {
        Http::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        app(BackboneApi::class)->http()
            ->get('ping');

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/ping' &&
                $request->hasHeader('Authorization', 'Bearer ::backbone-api-key::') &&
                $request->hasHeader('Accept', 'application/json') &&
                $request->hasHeader('Content-Type', 'application/json');
        });
    }

    #[Test]
    public function it_throws_an_exception_when_no_base_url_is_configured(): void
    {
        Http::fake();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No Backbone base URL configured in env!');

        app(BackboneApi::class)->http()
            ->get('ping');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_throws_an_exception_when_no_api_key_is_configured(): void
    {
        Http::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No Backbone API key configured in env!');

        app(BackboneApi::class)->http()
            ->get('ping');

        Http::assertNothingSent();
    }
}
