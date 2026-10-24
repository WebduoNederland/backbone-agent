<?php

namespace WebduoNederland\BackboneAgent\Tests\Pipelines\PhpFileScanner;

use Exception;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Pipelines\PhpFileScanner\ShutdownServerPipeline;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class ShutdownServerPipelineTest extends TestCase
{
    #[Test]
    public function it_runs_the_configured_shutdown_command_and_continues_the_pipeline(): void
    {
        Process::fake();

        config()->set('backbone-agent.php_file_scanner.shutdown_command', '::shutdown-command::');

        $result = app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php'], $result);

        Process::assertRan('::shutdown-command::');
    }

    #[Test]
    public function it_reports_when_the_server_could_not_be_shut_down(): void
    {
        Exceptions::fake();

        Process::fake([
            '*' => Process::result(errorOutput: 'sudo: a password is required', exitCode: 1),
        ]);

        $result = app(ShutdownServerPipeline::class)->handle(['shell.php'], fn (array $files): array => $files);

        $this->assertSame(['shell.php'], $result);

        Exceptions::assertReported(fn (Exception $exception): bool => $exception->getMessage() === 'Unable to shut down the server: sudo: a password is required');
    }
}
