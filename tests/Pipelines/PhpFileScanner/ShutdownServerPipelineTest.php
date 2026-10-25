<?php

namespace WebduoNederland\BackboneAgent\Tests\Pipelines\PhpFileScanner;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;
use WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner\ShutdownServerPipeline;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class ShutdownServerPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');
    }

    #[Test]
    public function it_runs_the_configured_shutdown_command_and_continues_the_pipeline(): void
    {
        Process::fake();

        Http::fake([
            '*' => Http::response(['success' => true, 'message' => '', 'paused' => false]),
        ]);

        config()->set('backbone-agent.php_file_scanner.shutdown_command', '::shutdown-command::');

        $result = app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php'], $result);

        Process::assertRan('::shutdown-command::');

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/v1/php-file-scanner/panic-mode' &&
                $request->method() === 'GET';
        });
    }

    #[Test]
    public function it_does_not_shut_down_the_server_when_panic_mode_is_paused_in_backbone(): void
    {
        Process::fake();

        Http::fake([
            '*' => Http::response(['success' => true, 'message' => '', 'paused' => true]),
        ]);

        $result = app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php'], $result);

        Process::assertNothingRan();
    }

    #[Test]
    public function it_limits_the_duration_of_the_api_call(): void
    {
        Process::fake();

        $pendingRequest = $this->mock(PendingRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('connectTimeout')->once()->with(5)->andReturnSelf();
            $mock->shouldReceive('timeout')->once()->with(5)->andReturnSelf();
            $mock->shouldReceive('get')->once()->with('v1/php-file-scanner/panic-mode')->andThrow(new ConnectionException('::connection-timed-out::'));
        });

        $this->mock(BackboneApi::class)
            ->shouldReceive('http')
            ->once()
            ->andReturn($pendingRequest);

        app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        Process::assertRan(config()->string('backbone-agent.php_file_scanner.shutdown_command'));
    }

    #[Test]
    public function it_shuts_down_the_server_when_backbone_is_unreachable(): void
    {
        Exceptions::fake();

        Process::fake();

        Http::fake(function (): void {
            throw new ConnectionException('::connection-timed-out::');
        });

        app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        Process::assertRan(config()->string('backbone-agent.php_file_scanner.shutdown_command'));

        Exceptions::assertReported(ConnectionException::class);
    }

    #[Test]
    public function it_shuts_down_the_server_when_backbone_responds_with_an_error(): void
    {
        Exceptions::fake();

        Process::fake();

        Http::fake([
            '*' => Http::response(['success' => false, 'message' => 'Invalid bearer token given!'], 401),
        ]);

        app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        Process::assertRan(config()->string('backbone-agent.php_file_scanner.shutdown_command'));

        Exceptions::assertReported(RequestException::class);
    }

    #[Test]
    public function it_shuts_down_the_server_when_backbone_is_not_configured(): void
    {
        Exceptions::fake();

        Process::fake();

        Http::fake();

        config()->set('backbone-agent.base_url', '');

        app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        Process::assertRan(config()->string('backbone-agent.php_file_scanner.shutdown_command'));

        Exceptions::assertReported(fn (Exception $exception): bool => $exception->getMessage() === 'No Backbone base URL configured in env!');

        Http::assertNothingSent();
    }

    #[Test]
    public function it_reports_when_the_server_could_not_be_shut_down(): void
    {
        Exceptions::fake();

        Process::fake([
            '*' => Process::result(errorOutput: 'sudo: a password is required', exitCode: 1),
        ]);

        Http::fake([
            '*' => Http::response(['success' => true, 'message' => '', 'paused' => false]),
        ]);

        $result = app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php'], $result);

        Exceptions::assertReported(fn (Exception $exception): bool => $exception->getMessage() === 'Unable to shut down the server: sudo: a password is required');
    }
}
