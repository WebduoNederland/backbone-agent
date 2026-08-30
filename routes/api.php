<?php

use Illuminate\Support\Facades\Route;
use WebduoNederland\BackboneAgent\Controllers\InfoController;
use WebduoNederland\BackboneAgent\Middleware\VerifyApiTokenMiddleware;

Route::prefix('v1')->middleware(VerifyApiTokenMiddleware::class)->group(function (): void {
    Route::get('info', [InfoController::class, 'get']);
});
