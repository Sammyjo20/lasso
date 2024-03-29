<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Commands\PullCommand;
use Sammyjo20\Lasso\Commands\PublishCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class LassoServiceProvider extends BaseServiceProvider
{
    /**
     * Register the Lasso service provider
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lasso.php', 'lasso');
    }

    /**
     * Boot the Lasso service provider
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Publish the config

        $this->publishes([
            __DIR__ . '/../config/lasso.php' => config_path('lasso.php'),
        ], 'lasso-config');

        // Register Lasso's commands

        $this->commands([
            PublishCommand::class,
            PullCommand::class,
        ]);

        // Bind Artisan and Filesystem to the container

        $this->app->singleton(Artisan::class, static fn () => new Artisan);
        $this->app->singleton(Filesystem::class, static fn () => new Filesystem);
    }
}
