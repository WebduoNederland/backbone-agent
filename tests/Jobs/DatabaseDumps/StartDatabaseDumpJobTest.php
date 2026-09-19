<?php

namespace WebduoNederland\BackboneAgent\Tests\Jobs\DatabaseDumps;

use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\DatabaseDumps\StartDatabaseDump;
use WebduoNederland\BackboneAgent\Jobs\DatabaseDumps\StartDatabaseDumpJob;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class StartDatabaseDumpJobTest extends TestCase
{
    #[Test]
    public function it_calls_the_start_database_dump_action(): void
    {
        $this->mock(StartDatabaseDump::class)
            ->shouldReceive('start')
            ->once()
            ->withArgs(function (int $id, string $fileName, array $excludeTables, array $sftpCredentials) {
                return
                    $id === 1 &&
                    $fileName === '::file-name::' &&
                    $excludeTables === ['::table-1::', '::table-2::'] &&
                    $sftpCredentials === ['host' => '::host::', 'username' => '::username::', 'password' => '::password::'];
            });

        StartDatabaseDumpJob::dispatch(
            1,
            '::file-name::',
            ['::table-1::', '::table-2::'],
            [
                'host' => '::host::',
                'username' => '::username::',
                'password' => '::password::',
            ]
        );
    }

    #[Test]
    public function it_uses_the_correct_queue(): void
    {
        $job = new StartDatabaseDumpJob(1, '::file-name::', [], []);

        $expectedQueue = config()->string('backbone-agent.database_dumps.queue');

        $this->assertEquals($expectedQueue, $job->queue);
    }

    #[Test]
    public function it_calls_the_api_when_the_job_fails(): void
    {
        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        Http::fake();

        $job = new StartDatabaseDumpJob(1, '::file-name::', [], []);

        $job->failed(new Exception('::exception-message::'));

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/v1/database-dumps/1/status' &&
                $request->method() === 'PATCH' &&
                $request->body() === '{"status":"failed","message":"::exception-message::"}';
        });
    }
}
