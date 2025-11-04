<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Generators\Commands\GenerateCrudCommand;
use App\Generators\Commands\GenerateScaffoldCommand;

class GeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateCrudCommand::class,
                GenerateScaffoldCommand::class,
            ]);
        }
    }
}
