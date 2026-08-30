<?php

namespace WebduoNederland\BackboneAgent\Tests\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use WebduoNederland\BackboneAgent\Middleware\VerifyApiTokenMiddleware;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class VerifyApiTokenMiddlewareTest extends TestCase
{
    #[Test]
    public function it_fails_when_no_bearer_token_is_given(): void
    {
        $request = new Request;

        $middleware = new VerifyApiTokenMiddleware;

        $response = $middleware->handle($request, function (): void {
            //
        });

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(401, $response->status());

        $this->assertEquals('{"success":false,"message":"No bearer token given!"}', $response->content());
    }

    #[Test]
    public function it_fails_when_no_api_key_is_configured(): void
    {
        $request = new Request;
        $request->headers->set('Authorization', 'Bearer ::api-key::');

        $middleware = new VerifyApiTokenMiddleware;

        $response = $middleware->handle($request, function (): void {
            //
        });

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(500, $response->status());

        $this->assertEquals('{"success":false,"message":"No API key configured in the env!"}', $response->content());
    }

    #[Test]
    public function it_fails_when_the_given_bearer_token_is_invalid(): void
    {
        config()->set('backbone-agent.api_key', '::actual-api-key::');

        $request = new Request;
        $request->headers->set('Authorization', 'Bearer ::invalid-api-key::');

        $middleware = new VerifyApiTokenMiddleware;

        $response = $middleware->handle($request, function (): void {
            //
        });

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(401, $response->status());

        $this->assertEquals('{"success":false,"message":"Invalid bearer token given!"}', $response->content());
    }

    #[Test]
    public function it_runs_the_given_closure_when_the_given_bearer_token_is_valid(): void
    {
        config()->set('backbone-agent.api_key', '::api-key::');

        $request = new Request;
        $request->headers->set('Authorization', 'Bearer ::api-key::');

        $middleware = new VerifyApiTokenMiddleware;

        $response = $middleware->handle($request, function (): Response {
            return response()->json([
                'success' => true,
                'message' => 'Hello world!',
            ]);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(200, $response->status());

        $this->assertEquals('{"success":true,"message":"Hello world!"}', $response->content());
    }
}
