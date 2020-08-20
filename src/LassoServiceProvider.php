<?php

namespace Sammyjo20\Lasso;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sammyjo20\Lasso\Commands\PublishCommand;
use Sammyjo20\Lasso\Commands\FetchCommand;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\ConfigValidator;

class LassoServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('lasso.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->app->instance(Console::class, new Console());

            (new ConfigValidator())->validate();

            $this->commands([
                PublishCommand::class,
                FetchCommand::class,
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