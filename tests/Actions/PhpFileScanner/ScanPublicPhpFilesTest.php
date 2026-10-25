<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions\PhpFileScanner;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\FindPublicPhpFiles;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\ScanPublicPhpFiles;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class ScanPublicPhpFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Process::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');
    }

    #[Test]
    public function it_does_nothing_when_no_php_files_are_found(): void
    {
        config()->set('backbone-agent.php_file_scanner.panic_mode', true);

        $this->mock(FindPublicPhpFiles::class)
            ->shouldReceive('find')
            ->once()
            ->andReturn([]);

        app(ScanPublicPhpFiles::class)->scan();

        Http::assertNothingSent();
        Process::assertNothingRan();
    }

    #[Test]
    public function it_notifies_backbone_without_shutting_down_when_panic_mode_is_disabled(): void
    {
        $this->mock(FindPublicPhpFiles::class)
            ->shouldReceive('find')
            ->once()
            ->andReturn(['shell.php']);

        app(ScanPublicPhpFiles::class)->scan();

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/v1/php-file-scanner/detected' &&
                $request->method() === 'POST' &&
                $request->body() === '{"files":["shell.php"]}';
        });

        Process::assertNothingRan();
    }

    #[Test]
    public function it_notifies_backbone_and_shuts_down_the_server_when_panic_mode_is_enabled(): void
    {
        config()->set('backbone-agent.php_file_scanner.panic_mode', true);

        $this->mock(FindPublicPhpFiles::class)
            ->shouldReceive('find')
            ->once()
            ->andReturn(['shell.php']);

        app(ScanPublicPhpFiles::class)->scan();

        Http::assertSentCount(2);

        Process::assertRan('sudo -n shutdown -h now');
    }
}
