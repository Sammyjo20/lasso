<?php

namespace Sammyjo20\Lasso;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sammyjo20\Lasso\Commands\PublishCommand;
use Sammyjo20\Lasso\Commands\PullCommand;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;

class LassoServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('lasso.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->bindsServices();

            $this->commands([
                PublishCommand::class,
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

    protected function bindsServices(): void
    {
        $this->app->instance(Artisan::class, new Artisan);
        $this->app->instance(Filesystem::class, new Filesystem);
    }
}
