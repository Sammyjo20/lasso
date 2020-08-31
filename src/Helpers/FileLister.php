<?php

namespace Sammyjo20\Lasso\Helpers;

use Symfony\Component\Finder\Finder;

final class FileLister
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
        $this->finder = (new Finder())->files()
            ->ignoreDotFiles(false)
            ->in($directory);
    }

    /**
     * @return Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }
}
