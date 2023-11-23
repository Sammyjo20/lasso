<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use ZipArchive;

class Zip
{
    /**
     * Base ZipArchive class
     */
    protected ZipArchive $zip;

    /**
     * Lasso Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * A big thank you to Spatie for their amazing "Laravel Backup" package.
     * A lot of the Zip code is inspired by their code.
     *
     * https://github.com/spatie/laravel-backup/blob/18cf209be56bb086aaeb1397e142c2a7805802b3/src/Tasks/Backup/Zip.php
     *
     * ZipFactory constructor.
     */
    public function __construct(string $destinationPath)
    {
        $this->filesystem = resolve(Filesystem::class);

        $this->createBaseZip($destinationPath);
    }

    /**
     * Add files from a given directory
     */
    public function addFilesFromDirectory(string $directory): self
    {
        $files = (new FileLister($directory))->getFinder();

        foreach ($files as $file) {
            $this->zip->addFile(str_replace('\\', '/', $file->getPathname()), str_replace('\\', '/', $file->getRelativePathname()));
        }

        return $this;
    }

    /**
     * Create base ZipArchive
     */
    private function createBaseZip(string $destination): void
    {
        $this->zip = (new ZipArchive);
        $this->zip->open($destination, ZipArchive::CREATE);
    }

    /**
     * Close the Zip
     */
    public function closeZip(): void
    {
        $this->zip->close();
    }
}
