<?php

namespace WebduoNederland\BackboneAgent\Controllers;

use Illuminate\Http\JsonResponse;
use WebduoNederland\BackboneAgent\Actions\GetApplicationInfo;

class InfoController
{
    public function get(): JsonResponse
    {
        $data = app(GetApplicationInfo::class)->get();

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data,
        ]);
    }
}
