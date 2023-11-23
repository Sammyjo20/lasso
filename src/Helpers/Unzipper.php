<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use ZipArchive;

/**
 * @internal
 */
class Unzipper
{
    /**
     * @var ZipArchive
     */
    protected ZipArchive $zip;

    /**
     * @var string
     */
    protected string $source;

    /**
     * @var string
     */
    protected string $destination;

    /**
     * @var Filesystem
     */
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
     *
     * @return void
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
