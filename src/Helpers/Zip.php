<?php


namespace Sammyjo20\Lasso\Helpers;

use ZipArchive;

class Zip
{
    /**
     * @var ZipArchive
     */
    protected $zip;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * A big thank you to Spatie for their amazing "Laravel Backup" package.
     * A lot of the ZipArchive code is inspired by their code.
     *
     * https://github.com/spatie/laravel-backup/blob/18cf209be56bb086aaeb1397e142c2a7805802b3/src/Tasks/Backup/Zip.php
     *
     * ZipFactory constructor.
     * @param string $destinationPath
     */
    public function __construct(string $destinationPath)
    {
        $this->setFilesystem()
            ->createBaseZip($destinationPath);
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function addFilesFromDirectory(string $directory): self
    {
        $files = (new FileLister($directory))
            ->getFinder();

        foreach ($files as $file) {
            $this->zip->addFile(str_replace('\\', '/', $file->getPathname()), str_replace('\\', '/', $file->getRelativePathname()));
        }

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
     * @param string $destination
     * @return $this
     */
    private function createBaseZip(string $destination): self
    {
        $this->zip = new ZipArchive();
        $this->zip->open($destination, ZipArchive::CREATE);

        return $this;
    }

    /**
     * @return void
     */
    public function closeZip(): void
    {
        $this->zip->close();
    }
}
