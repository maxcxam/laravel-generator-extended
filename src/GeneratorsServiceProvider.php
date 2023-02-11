<?php

namespace Maxcxam\Generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEntityGenerator();
    }

    /**
     * Register the make:seed generator.
     */
    private function registerEntityGenerator()
    {
        $this->app->singleton('command.maxcxam.entity', function ($app) {
            return $app['Maxcxam\Generators\Commands\MakeEntity'];
        });

        $this->commands('command.maxcxam.entity');
    }

    /**
     * Register the make:migration generator.
     */
    private function registerMigrationGenerator()
    {
        $this->app->singleton('command.laracasts.migrate', function ($app) {
            return $app['Laracasts\Generators\Commands\MigrationMakeCommand'];
        });

        $this->commands('command.laracasts.migrate');
    }
}
