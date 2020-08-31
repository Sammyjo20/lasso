<?php

namespace Sammyjo20\Lasso\Factories;

use ZipArchive;
use Illuminate\Filesystem\Filesystem;

final class ZipExtractor
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
     * ZipExtractor constructor.
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
    private function closeZip(): void
    {
        $this->zip_file->close();
    }

    /**
     * @param string $destination
     * @return void
     */
    public function extractTo(string $destination): void
    {
        $this->filesystem->ensureDirectoryExists($destination);

        $this->zip_file->extractTo($destination);

        $this->closeZip();
    }
}
