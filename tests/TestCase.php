<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Tests;

use Sammyjo20\Lasso\LassoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get the package providers
     */
    protected function getPackageProviders($app): array
    {
        return [
            LassoServiceProvider::class,
        ];
    }

    /**
     * Get environment setup
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.assets', [
            'driver' => 'local',
            'root' => realpath(__DIR__ . '/Fixtures/Cloud'),
            'throw' => false,
        ]);

        $app->setBasePath(__DIR__ . '/../');
    }
}
