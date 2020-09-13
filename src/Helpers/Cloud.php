<?php

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Helpers\Filesystem as LocalFilesystem;

class Cloud
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $cloudFilesystem;

    /**
     * @var string
     */
    protected $cloudDisk;

    /**
     * @var LocalFilesystem
     */
    protected $localFilesystem;

    /**
     * Cloud constructor.
     */
    public function __construct()
    {
        $this->setCloudDisk(config('lasso.storage.disk'));

        $this->initCloudFilesystem()
            ->initLocalFilesystem();
    }

    /**
     * @param string $path
     * @param string $name
     */
    public function uploadFile(string $path, string $name): void
    {
        $upload_path = $this->localFilesystem->getUploadPath($name);

        $stream = fopen($path, 'rb');

        // Use the stream to write the bundle to the Filesystem.
        $this->cloudFilesystem->putStream($upload_path, $stream);

        // Close the Stream pointer because it's good practice.
        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    /**
     * @return string
     */
    public function getCloudDisk(): string
    {
        return $this->cloudDisk;
    }

    /**
     * @param string $disk
     * @return $this
     */
    public function setCloudDisk(string $disk): self
    {
        $this->cloudDisk = $disk;

        return $this;
    }

    /**
     * @return $this
     */
    public function initCloudFilesystem(): self
    {
        $this->cloudFilesystem = Storage::disk($this->cloudDisk);

        return $this;
    }

    /**
     * @return $this
     */
    public function initLocalFilesystem(): self
    {
        $this->localFilesystem = resolve(LocalFilesystem::class);

        return $this;
    }
}
