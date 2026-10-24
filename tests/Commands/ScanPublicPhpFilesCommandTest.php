<?php

namespace WebduoNederland\BackboneAgent\Tests\Commands;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Jobs\PhpFileScanner\ScanPublicPhpFilesJob;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class ScanPublicPhpFilesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_the_scan_public_php_files_job(): void
    {
        Bus::fake();

        $this->artisan('backbone-agent:scan-public-php-files');

        Bus::assertDispatched(ScanPublicPhpFilesJob::class);
    }

    #[Test]
    public function it_returns_a_successful_exit_code(): void
    {
        Bus::fake();

        $this->assertSame(0, Artisan::call('backbone-agent:scan-public-php-files'));
    }

    #[Test]
    public function it_is_scheduled_every_fifteen_seconds_even_in_maintenance_mode(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn (Event $event): bool => str_contains((string) $event->command, 'backbone-agent:scan-public-php-files'));

        $this->assertCount(1, $events);

        /** @var Event $event */
        $event = $events->first();

        $this->assertSame(15, $event->repeatSeconds);
        $this->assertTrue($event->evenInMaintenanceMode);
    }
}
