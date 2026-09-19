<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions\DatabaseDumps;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\DatabaseDumps\UploadDatabaseDump;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class UploadDatabaseDumpTest extends TestCase
{
    protected const SFTP_CREDENTIALS = [
        'host' => '::host::',
        'username' => '::username::',
        'password' => '::password::',
    ];

    #[Test]
    public function it_uploads_the_file_to_sftp_and_deletes_the_local_file(): void
    {
        $localDisk = Storage::fake('local-test');
        $sftpDisk = Storage::fake('sftp-test');

        $localDisk->put('dump.sql', '::dump-contents::');

        $this->bindDrivers($localDisk, $sftpDisk);

        app(UploadDatabaseDump::class)->upload(self::SFTP_CREDENTIALS, 'dump.sql');

        $sftpDisk->assertExists('dump.sql');
        $this->assertSame('::dump-contents::', $sftpDisk->get('dump.sql'));
        $localDisk->assertMissing('dump.sql');
    }

    #[Test]
    public function it_throws_an_exception_when_the_local_file_cannot_be_read(): void
    {
        $localDisk = Storage::fake('local-test');
        $sftpDisk = Storage::fake('sftp-test');

        $this->bindDrivers($localDisk, $sftpDisk);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to read file for upload: missing.sql');

        app(UploadDatabaseDump::class)->upload(self::SFTP_CREDENTIALS, 'missing.sql');
    }

    protected function bindDrivers(FilesystemAdapter $localDisk, FilesystemAdapter $sftpDisk): void
    {
        Storage::shouldReceive('createLocalDriver')
            ->once()
            ->with(['driver' => 'local', 'root' => base_path()], 'base_path_driver')
            ->andReturn($localDisk);

        Storage::shouldReceive('createSftpDriver')
            ->once()
            ->with(self::SFTP_CREDENTIALS)
            ->andReturn($sftpDisk);
    }
}
