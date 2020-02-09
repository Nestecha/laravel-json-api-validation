<?php

namespace Nestecha\LaravelJsonApiValidation;

use Illuminate\Support\ServiceProvider;

class LaravelJsonApiValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__.'/../config/config.php' => config_path('json-api-validation.php'),
                ],
                'config'
            );
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
