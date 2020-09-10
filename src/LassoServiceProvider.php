<?php

namespace Sammyjo20\Lasso;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sammyjo20\Lasso\Commands\PublishCommand;
use Sammyjo20\Lasso\Commands\PullCommand;
use Sammyjo20\Lasso\Container\Console;

class LassoServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->registerCommands();
        $this->offerPublishing();
    }

    public function register()
    {
        $this->configure();
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'lasso'
        );
    }

    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('lasso.php'),
            ], 'lasso-config');
        }
    }


    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishCommand::class,
                PullCommand::class,
            ]);
        }
    }
}
