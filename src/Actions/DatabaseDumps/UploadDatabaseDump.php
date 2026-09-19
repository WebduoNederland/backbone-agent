<?php

namespace WebduoNederland\BackboneAgent\Actions\DatabaseDumps;

use Exception;
use Illuminate\Support\Facades\Storage;

class UploadDatabaseDump
{
    public function upload(array $sftpCredentials, string $fileName): void
    {
        $localDisk = Storage::createLocalDriver([
            'driver' => 'local',
            'root' => base_path(),
        ], 'base_path_driver');

        $sftpDisk = Storage::createSftpDriver($sftpCredentials);

        $stream = $localDisk->readStream($fileName);

        if ($stream === null) {
            throw new Exception('Unable to read file for upload: '.$fileName);
        }

        $sftpDisk->put($fileName, $stream);

        $localDisk->delete($fileName);
    }
}
