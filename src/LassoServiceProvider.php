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
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lasso.php',
            'lasso'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands()
                ->offerPublishing()
                ->bindsServices();
        }
    }

    /**
     * @return $this
     */
    protected function registerCommands(): self
    {
        $this->commands([
            PublishCommand::class,
            PullCommand::class,
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    protected function offerPublishing(): self
    {
        $this->publishes([
            __DIR__ . '/../config/lasso.php' => config_path('lasso.php'),
        ], 'lasso-config');

        return $this;
    }

    /**
     * @return $this
     */
    protected function bindsServices(): self
    {
        $this->app->singleton(Artisan::class, function () {
            return new Artisan;
        });

        $this->app->singleton(Filesystem::class, function () {
            return new Filesystem;
        });

        return $this;
    }
}
