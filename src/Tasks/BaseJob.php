<?php

namespace Sammyjo20\Lasso\Tasks;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Interfaces\JobInterface;

abstract class BaseJob implements JobInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

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
}
