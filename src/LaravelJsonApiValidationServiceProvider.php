<?php

namespace Nestecha\LaravelJsonApiValidation;

use Illuminate\Support\ServiceProvider;
use CloudCreativity\LaravelJsonApi\Factories\Factory as CloudCreativityFactory;
use CloudCreativity\LaravelJsonApi\Document\Error\Translator as CloudCreativityTranslator;
use CloudCreativity\LaravelJsonApi\Validation\Validator as CloudCreativityValidator;

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
        $this->app->bind(CloudCreativityFactory::class, Factory::class);
        $this->app->bind(CloudCreativityTranslator::class, Translator::class);
        $this->app->bind(CloudCreativityValidator::class, Validator::class);
    }
}
