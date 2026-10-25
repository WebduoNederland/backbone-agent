<?php

namespace WebduoNederland\BackboneAgent\Tests\Jobs\PhpFileScanner;

use Laravel\Horizon\Contracts\Silenced;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\ScanPublicPhpFiles;
use WebduoNederland\BackboneAgent\Jobs\PhpFileScanner\ScanPublicPhpFilesJob;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class ScanPublicPhpFilesJobTest extends TestCase
{
    #[Test]
    public function it_calls_the_scan_public_php_files_action(): void
    {
        $this->mock(ScanPublicPhpFiles::class)
            ->shouldReceive('scan')
            ->once();

        ScanPublicPhpFilesJob::dispatch();
    }

    #[Test]
    public function it_uses_the_correct_queue(): void
    {
        $job = new ScanPublicPhpFilesJob;

        $expectedQueue = config()->string('backbone-agent.php_file_scanner.queue');

        $this->assertEquals($expectedQueue, $job->queue);
    }

    #[Test]
    public function it_only_tries_once(): void
    {
        $this->assertSame(1, (new ScanPublicPhpFilesJob)->tries());
    }

    #[Test]
    public function it_is_silenced_in_horizon(): void
    {
        $this->assertInstanceOf(Silenced::class, new ScanPublicPhpFilesJob);
    }
}
