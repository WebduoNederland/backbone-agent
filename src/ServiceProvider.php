<?php

namespace WebduoNederland\BackboneAgent;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this
            ->bootConfig()
            ->bootRoutes();
    }

    protected function bootConfig(): self
    {
        $this->publishes([
            __DIR__.'/../config/backbone-agent.php' => config_path('backbone-agent.php'),
        ], 'config');

        return $this;
    }

    protected function bootRoutes(): self
    {
        Route::prefix('api/backbone')->middleware('api')->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });

        return $this;
    }

    public function register(): void
    {
        $this
            ->registerConfig();
    }

    protected function registerConfig(): self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/backbone-agent.php', 'backbone-agent');

        return $this;
    }
}
