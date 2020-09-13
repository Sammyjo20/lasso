<?php

namespace Sammyjo20\Lasso\Helpers;

use Symfony\Component\Finder\Finder;

class FileLister
{
    /**
     * @var Finder
     */
    public $finder;

    /**
     * FileLister constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->finder = (new Finder())
            ->in($directory)
            ->ignoreDotFiles(false)
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->files();
    }

    /**
     * @return Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }
}
