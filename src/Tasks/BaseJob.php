<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Tasks;

use Sammyjo20\Lasso\Helpers\Cloud;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;

abstract class BaseJob
{
    /**
     * Lasso Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Lasso Cloud
     */
    protected Cloud $cloud;

    /**
     * Lasso Artisan
     */
    protected Artisan $artisan;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->artisan = resolve(Artisan::class);
        $this->filesystem = resolve(Filesystem::class);
        $this->cloud = new Cloud;
    }
}
