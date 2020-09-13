<?php

namespace Sammyjo20\Lasso\Tasks;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Cloud;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Interfaces\JobInterface;

abstract class BaseJob implements JobInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Cloud
     */
    protected $cloud;

    /**
     * @var Artisan
     */
    protected $artisan;

    /**
     * BaseJob constructor.
     */
    public function __construct()
    {
        $this->setArtisan()
            ->setFilesystem();

        // The Cloud class should be defined after the Filesystem as
        // it depends on the Filesystem.

        $this->setCloud();
    }

    /**
     * @return $this
     */
    private function setArtisan(): self
    {
        $this->artisan = resolve(Artisan::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function setFilesystem(): self
    {
        $this->filesystem = resolve(Filesystem::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function setCloud(): self
    {
        $this->cloud = new Cloud;

        return $this;
    }
}
