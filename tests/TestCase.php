<?php

namespace Sammyjo20\Lasso\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sammyjo20\Lasso\LassoServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            LassoServiceProvider::class,
        ];
    }
}
