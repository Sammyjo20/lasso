<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
class FileLister
{
    /**
     * Symfony Finder
     */
    public Finder $finder;

    /**
     * FileLister constructor.
     */
    public function __construct(string $directory)
    {
        $this->finder = (new Finder)
            ->in($directory)
            ->ignoreDotFiles(false)
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->files();
    }

    /**
     * Return Finder
     */
    public function getFinder(): Finder
    {
        return $this->finder;
    }
}
