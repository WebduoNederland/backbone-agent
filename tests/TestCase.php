<?php

namespace WebduoNederland\BackboneAgent\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;
use WebduoNederland\BackboneAgent\ServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        Http::preventStrayRequests();
    }
}
