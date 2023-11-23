<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use ZipArchive;

class Unzipper
{
    /**
     * @var ZipArchive
     */
    protected $zip;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Unzipper constructor.
     */
    public function __construct(string $source, string $destination)
    {
        $this->setFilesystem()
            ->createBaseZip($source)
            ->setDestination($destination);
    }

    
    public function run(): void
    {
        $this->filesystem->ensureDirectoryExists($this->destination);
        $this->zip->extractTo($this->destination);

        $this->closeZip();
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
    private function createBaseZip(string $destination): self
    {
        $this->zip = new ZipArchive();
        $this->zip->open($destination, ZipArchive::CREATE);

        return $this;
    }

    /**
     * @return $this
     */
    private function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    
    public function closeZip(): void
    {
        $this->zip->close();
    }
}
