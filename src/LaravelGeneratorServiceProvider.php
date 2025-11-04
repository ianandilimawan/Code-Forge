<?php

namespace IanAndilimawan\LaravelGenerator;

use Illuminate\Support\ServiceProvider;
use IanAndilimawan\LaravelGenerator\Commands\GenerateCrudCommand;
use IanAndilimawan\LaravelGenerator\Commands\GenerateScaffoldCommand;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateCrudCommand::class,
                GenerateScaffoldCommand::class,
            ]);
        }

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'code-forge-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'code-forge-seeders');

        // Publish models
        $this->publishes([
            __DIR__ . '/../src/Models' => app_path('Models'),
        ], 'code-forge-models');

        // Publish services
        $this->publishes([
            __DIR__ . '/../src/Services' => app_path('Services'),
        ], 'code-forge-services');

        // Publish components
        $this->publishes([
            __DIR__ . '/../resources/views/components' => resource_path('views/components'),
        ], 'code-forge-components');

        // Publish stubs
        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('stubs/code-forge'),
        ], 'code-forge-stubs');

        // Publish all assets at once
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/seeders' => database_path('seeders'),
            __DIR__ . '/../src/Models' => app_path('Models'),
            __DIR__ . '/../src/Services' => app_path('Services'),
            __DIR__ . '/../resources/views/components' => resource_path('views/components'),
            __DIR__ . '/../stubs' => resource_path('stubs/code-forge'),
        ], 'code-forge');
    }
}
