<?php

namespace WebduoNederland\BackboneAgent\Tests\Pipelines\PhpFileScanner;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;
use WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner\NotifyBackbonePipeline;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class NotifyBackbonePipelineTest extends TestCase
{
    #[Test]
    public function it_notifies_backbone_and_continues_the_pipeline(): void
    {
        Http::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        $result = app(NotifyBackbonePipeline::class)->handle(['shell.php', 'uploads/evil.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php', 'uploads/evil.php'], $result);

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/v1/php-file-scanner/detected' &&
                $request->method() === 'POST' &&
                $request->body() === '{"files":["shell.php","uploads\/evil.php"]}';
        });
    }

    #[Test]
    public function it_limits_the_duration_of_the_api_call(): void
    {
        $pendingRequest = $this->mock(PendingRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('connectTimeout')->once()->with(5)->andReturnSelf();
            $mock->shouldReceive('timeout')->once()->with(5)->andReturnSelf();
            $mock->shouldReceive('post')->once()->with('v1/php-file-scanner/detected', ['files' => ['shell.php']]);
        });

        $this->mock(BackboneApi::class)
            ->shouldReceive('http')
            ->once()
            ->andReturn($pendingRequest);

        app(NotifyBackbonePipeline::class)->handle(['shell.php'], fn (array $files): array => $files);
    }

    #[Test]
    public function it_continues_the_pipeline_when_backbone_is_unreachable(): void
    {
        Exceptions::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        Http::fake(function (): void {
            throw new ConnectionException('::connection-timed-out::');
        });

        $result = app(NotifyBackbonePipeline::class)->handle(['shell.php'], fn (array $files): string => '::next::');

        $this->assertSame('::next::', $result);

        Exceptions::assertReported(ConnectionException::class);
    }

    #[Test]
    public function it_continues_the_pipeline_when_backbone_is_not_configured(): void
    {
        Exceptions::fake();

        Http::fake();

        $result = app(NotifyBackbonePipeline::class)->handle(['shell.php'], fn (array $files): string => '::next::');

        $this->assertSame('::next::', $result);

        Exceptions::assertReported(fn (Exception $exception): bool => $exception->getMessage() === 'No Backbone base URL configured in env!');

        Http::assertNothingSent();
    }
}
