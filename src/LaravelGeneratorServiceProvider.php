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
        ], 'laravel-generator-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'laravel-generator-seeders');

        // Publish models
        $this->publishes([
            __DIR__ . '/../src/Models' => app_path('Models'),
        ], 'laravel-generator-models');

        // Publish services
        $this->publishes([
            __DIR__ . '/../src/Services' => app_path('Services'),
        ], 'laravel-generator-services');

        // Publish components
        $this->publishes([
            __DIR__ . '/../resources/views/components' => resource_path('views/components'),
        ], 'laravel-generator-components');

        // Publish stubs
        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('stubs/laravel-generator'),
        ], 'laravel-generator-stubs');

        // Publish all assets at once
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/seeders' => database_path('seeders'),
            __DIR__ . '/../src/Models' => app_path('Models'),
            __DIR__ . '/../src/Services' => app_path('Services'),
            __DIR__ . '/../resources/views/components' => resource_path('views/components'),
            __DIR__ . '/../stubs' => resource_path('stubs/laravel-generator'),
        ], 'laravel-generator');
    }
}
