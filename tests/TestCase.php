<?php

namespace Sammyjo20\Lasso\Tests;

use Sammyjo20\Lasso\LassoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

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
