<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use LogicException;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;
use Illuminate\Contracts\Filesystem\Filesystem as BaseFilesystem;

/**
 * @internal
 */
class Cloud
{
    /**
     * Lasso Filesystem
     */
    protected BaseFilesystem $cloudFilesystem;

    /**
     * Local Filesystem
     */
    protected Filesystem $localFilesystem;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cloudFilesystem = Storage::disk(config('lasso.storage.disk'));
        $this->localFilesystem = resolve(Filesystem::class);
    }

    /**
     * Upload a file to the cloud filesystem
     */
    public function uploadFile(string $path, string $name): void
    {
        $uploadPath = $this->getUploadPath($name);

        $stream = fopen($path, 'rb');

        if (! $stream) {
            throw new LogicException('Unable to create a stream from the file path');
        }

        // Use the stream to write the bundle to the Filesystem.

        if ($this->cloudFilesystem->writeStream($uploadPath, $stream) === false) {
            throw new UnableToWriteFile(sprintf('Unable to write file at location [%s]', $uploadPath));
        }

        fclose($stream);
    }

    /**
     * Returns the Lasso upload directory.
     *
     * You can specify a file to create a fully qualified URL.
     */
    public function getUploadPath(string $file = null): string
    {
        $uploadPath = config('lasso.storage.upload_to');

        $directory = sprintf('%s/%s', $uploadPath, $this->localFilesystem->getLassoEnvironment());

        if (is_null($file)) {
            return $directory;
        }

        return $directory . '/' . ltrim($file, '/');
    }

    /**
     * Call a method on the base Filesystem
     *
     * @throws \Sammyjo20\Lasso\Exceptions\ConsoleMethodException
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->cloudFilesystem, $name)) {
            return call_user_func_array([$this->cloudFilesystem, $name], $arguments);
        }

        throw new ConsoleMethodException(sprintf('Method %s::%s does not exist.', get_class($this->cloudFilesystem), $name));
    }
}
