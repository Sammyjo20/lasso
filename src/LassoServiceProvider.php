<?php

namespace Sammyjo20\Lasso;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sammyjo20\Lasso\Commands\PushCommand;
use Sammyjo20\Lasso\Commands\PullCommand;
use Sammyjo20\Lasso\Container\Console;

class LassoServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('lasso.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->app->instance(Console::class, new Console());

            $this->commands([
                PushCommand::class,
                PullCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'lasso'
        );
    }
}
