<?php

namespace WebduoNederland\BackboneAgent\Tests\Controllers;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Jobs\DatabaseDumps\StartDatabaseDumpJob;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class DatabaseDumpControllerTest extends TestCase
{
    #[Test]
    public function it_dispatches_the_start_database_dump_job(): void
    {
        Bus::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        $response = $this->withHeader('Authorization', 'Bearer ::backbone-api-key::')
            ->postJson('api/backbone/v1/database-dump/start', [
                'id' => 1,
                'file_name' => '::file-name::',
                'exclude_tables' => ['::table-1::', '::table-2::'],
                'sftp_credentials' => ['host' => '::host::', 'username' => '::username::', 'password' => '::password::'],
            ]);

        Bus::assertDispatched(StartDatabaseDumpJob::class);

        $this->assertEquals([
            'success' => true,
            'message' => '',
        ], $response->json());
    }
}
