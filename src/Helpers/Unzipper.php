<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use ZipArchive;

/**
 * @internal
 */
class Unzipper
{
    
    protected ZipArchive $zip;

    
    protected string $source;

    
    protected string $destination;

    
    protected Filesystem $filesystem;

    /**
     * Unzipper constructor.
     */
    public function __construct(string $source, string $destination)
    {
        $this->filesystem = resolve(Filesystem::class);
        $this->destination = $destination;

        $this->createBaseZip($source);
    }

    /**
     * Unzip the source into the destination
     */
    public function run(): void
    {
        $this->filesystem->ensureDirectoryExists($this->destination);

        $this->zip->extractTo($this->destination);

        $this->zip->close();
    }

    /**
     * Create the base ZipArchive
     */
    private function createBaseZip(string $destination): void
    {
        $this->zip = new ZipArchive;
        $this->zip->open($destination, ZipArchive::CREATE);
    }
}
