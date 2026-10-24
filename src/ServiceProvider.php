<?php

namespace WebduoNederland\BackboneAgent;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use WebduoNederland\BackboneAgent\Commands\CheckShutdownPermissionCommand;
use WebduoNederland\BackboneAgent\Commands\ScanPublicPhpFilesCommand;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this
            ->bootConfig()
            ->bootRoutes()
            ->bootCommands()
            ->bootSchedule();
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

    protected function bootCommands(): self
    {
        $this->commands([
            CheckShutdownPermissionCommand::class,
            ScanPublicPhpFilesCommand::class,
        ]);

        return $this;
    }

    protected function bootSchedule(): self
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command(ScanPublicPhpFilesCommand::class)
                ->everyFifteenSeconds()
                ->evenInMaintenanceMode();
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
