<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions\DatabaseDumps;

use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Spatie\DbDumper\Databases\MySql;
use WebduoNederland\BackboneAgent\Actions\DatabaseDumps\StartDatabaseDump;
use WebduoNederland\BackboneAgent\Actions\DatabaseDumps\UploadDatabaseDump;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class StartDatabaseDumpTest extends TestCase
{
    #[Test]
    public function it_can_make_a_dump(): void
    {
        Http::fake();

        config()->set('backbone-agent.base_url', '::backbone-api::');
        config()->set('backbone-agent.api_key', '::backbone-api-key::');

        config()->set('backbone-agent.database_dumps.mysql_dump_binary_path', '::mysql-dump-binary-path::');
        config()->set('backbone-agent.database_dumps.mysql_socket_path', '::mysql-socket-path::');

        config()->set('database.default', 'mysql');

        $sftpCredentials = [
            'host' => '::host::',
            'username' => '::username::',
            'password' => '::password::',
        ];

        $this->mock(MySql::class, function (MockInterface $mock): void {
            $mock->shouldReceive('create')->once()->andReturnSelf();
            $mock->shouldReceive('useSingleTransaction')->once()->andReturnSelf();
            $mock->shouldReceive('setDbName')->once()->with('laravel')->andReturnSelf();
            $mock->shouldReceive('setUserName')->once()->with('root')->andReturnSelf();
            $mock->shouldReceive('setPassword')->once()->with('')->andReturnSelf();
            $mock->shouldReceive('excludeTables')->once()->with(['::table-1::', '::table-2::'])->andReturnSelf();
            $mock->shouldReceive('setDumpBinaryPath')->once()->with('::mysql-dump-binary-path::')->andReturnSelf();
            $mock->shouldReceive('setSocket')->once()->with('::mysql-socket-path::')->andReturnSelf();
            $mock->shouldReceive('dumpToFile')->once()->with(base_path('::file-name::'));
        });

        $this->mock(UploadDatabaseDump::class)
            ->shouldReceive('upload')
            ->once()
            ->with($sftpCredentials, '::file-name::');

        app(StartDatabaseDump::class)->start(1, '::file-name::', ['::table-1::', '::table-2::'], $sftpCredentials);

        Http::assertSent(function (Request $request): bool {
            return
                $request->url() === '::backbone-api::/api/v1/database-dumps/1/status' &&
                $request->method() === 'PATCH' &&
                $request->body() === '{"status":"finished","message":"Ok"}';
        });
    }

    #[Test]
    public function it_throws_an_exception_when_no_database_connection_is_found(): void
    {
        config()->set('database.connections', []);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No database connection found!');

        app(StartDatabaseDump::class)->start(1, '::file-name::', [], []);
    }

    #[Test]
    public function it_throws_an_exception_when_database_driver_is_not_mysql(): void
    {
        config()->set('database.default', 'pgsql');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database driver (pgsql) not supported!');

        app(StartDatabaseDump::class)->start(1, '::file-name::', [], []);
    }
}
