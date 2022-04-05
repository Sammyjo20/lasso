<?php

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;
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
     * @param $name
     * @param $arguments
     * @return mixed|void
     * @throws ConsoleMethodException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->cloudFilesystem, $name)) {
            return call_user_func_array([$this->cloudFilesystem, $name], $arguments);
        }

        throw new ConsoleMethodException(sprintf(
            'Method %s::%s does not exist.',
            get_class($this->cloudFilesystem),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $name
     */
    public function uploadFile(string $path, string $name): void
    {
        $upload_path = $this->getUploadPath($name);

        $stream = fopen($path, 'rb');

        // Use the stream to write the bundle to the Filesystem.
        $this->cloudFilesystem->writeStream($upload_path, $stream);

        // Close the Stream pointer because it's good practice.
        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    /**
     * Returns the Lasso upload directory. You can specify a file
     * to create a fully qualified URL.
     *
     * @param string|null $file
     * @return string
     */
    public function getUploadPath(string $file = null): string
    {
        $uploadPath = config('lasso.storage.upload_to');

        $dir = sprintf('%s/%s', $uploadPath, $this->localFilesystem->getLassoEnvironment());

        if (is_null($file)) {
            return $dir;
        }

        return $dir . '/' . ltrim($file, '/');
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
