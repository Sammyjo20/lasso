<?php

namespace Sammyjo20\Lasso\Factories;

use Illuminate\Filesystem\Filesystem;
use ZipArchive;

final class ZipFactory
{
    /**
     * @var ZipArchive
     */
    protected $zip_file;

    /**
     * @var string
     */
    protected $zip_path;

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
     * @param string $zip_path
     */
    public function __construct(string $zip_path)
    {
        $this->zip_file = new ZipArchive();

        $this->zip_path = $zip_path;

        $this->filesystem = new Filesystem();

        $this->openZip();
    }

    /**
     * @return void
     */
    private function openZip(): void
    {
        $this->zip_file->open($this->zip_path, ZipArchive::CREATE);
    }

    /**
     * @return void
     */
    public function closeZip(): void
    {
        $this->zip_file->close();
    }

    /**
     * @param string $path
     * @param string $relative_path
     * @return $this
     */
    public function add(string $path, string $relative_path): self
    {
        if ($this->filesystem->exists($path)) {
            $this->zip_file->addFile($path, ltrim($relative_path, DIRECTORY_SEPARATOR));
        }

        return $this;
    }
}
