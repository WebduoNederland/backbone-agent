<?php

namespace WebduoNederland\BackboneAgent\Actions\DatabaseDumps;

use Exception;
use Spatie\DbDumper\Databases\MySql;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;
use WebduoNederland\BackboneAgent\Enums\DatabaseDumpStatus;

class StartDatabaseDump
{
    public function start(int $id, string $fileName, array $excludeTables, array $sftpCredentials): void
    {
        $defaultConnectionName = config()->string('database.default', '');

        $connections = config()->array('database.connections', []);

        /** @var ?array $connection */
        $connection = data_get($connections, $defaultConnectionName);

        if ($connection === null) {
            throw new Exception('No database connection found!');
        }

        /** @var ?string $driver */
        $driver = data_get($connection, 'driver');

        if ($driver !== 'mysql') {
            throw new Exception('Database driver ('.($driver ?? 'unknown').') not supported!');
        }

        /** @var ?string $mysqlDumpBinaryPath */
        $mysqlDumpBinaryPath = config()->get('backbone-agent.database_dumps.mysql_dump_binary_path');

        /** @var ?string $mysqlSocketPath */
        $mysqlSocketPath = config()->get('backbone-agent.database_dumps.mysql_socket_path');

        $databaseName = data_get($connection, 'database');
        $databaseUsername = data_get($connection, 'username');
        $databasePassword = data_get($connection, 'password');

        $mysql = app(MySql::class)
            ->create()
            ->useSingleTransaction()
            ->setDbName($databaseName)
            ->setUserName($databaseUsername)
            ->setPassword($databasePassword)
            ->excludeTables($excludeTables);

        if ($mysqlDumpBinaryPath !== null) {
            $mysql->setDumpBinaryPath($mysqlDumpBinaryPath);
        }

        if ($mysqlSocketPath !== null) {
            $mysql->setSocket($mysqlSocketPath);
        }

        $mysql
            ->dumpToFile(base_path($fileName));

        app(UploadDatabaseDump::class)->upload($sftpCredentials, $fileName);

        app(BackboneApi::class)->http()
            ->patch('v1/database-dumps/'.$id.'/status', [
                'status' => DatabaseDumpStatus::Finished,
                'message' => 'Ok',
            ]);
    }
}
