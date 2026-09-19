<?php

namespace WebduoNederland\BackboneAgent\Controllers;

use Illuminate\Http\JsonResponse;
use WebduoNederland\BackboneAgent\Jobs\DatabaseDumps\StartDatabaseDumpJob;
use WebduoNederland\BackboneAgent\Requests\DatabaseDumpStartRequest;

class DatabaseDumpController
{
    public function start(DatabaseDumpStartRequest $request): JsonResponse
    {
        StartDatabaseDumpJob::dispatch(
            $request->id,
            $request->file_name,
            $request->exclude_tables,
            $request->sftp_credentials,
        );

        return response()->json([
            'success' => true,
            'message' => '',
        ]);
    }
}
