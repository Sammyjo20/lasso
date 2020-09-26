<?php

namespace Sammyjo20\Lasso\Helpers;

use \Illuminate\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    /**
     * @var string
     */
    protected $lassoEnvironment;

    /**
     * @var string
     */
    protected $cloudDisk;

    /**
     * @var string
     */
    protected $publicPath;

    /**
     * Filesystem constructor.
     */
    public function __construct()
    {
        $lassoEnvironment = config('lasso.storage.environment') ?? 'global';
        $cloudDisk = config('lasso.storage.disk');
        $publicPath = config('lasso.public_path');

        $this->setLassoEnvironment($lassoEnvironment)
            ->setCloudDisk($cloudDisk)
            ->setPublicPath($publicPath);
    }

    /**
     * @param $resource
     * @param string $destination
     * @return bool
     */
    public function putStream($resource, string $destination): bool
    {
        $stream = fopen($destination, 'w+b');

        if (! $stream || stream_copy_to_stream($resource, $stream) === false || ! fclose($stream)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $bundle
     */
    public function createFreshLocalBundle(array $bundle): void
    {
        $this->deleteLocalBundle();

        $this->put(base_path('lasso-bundle.json'), json_encode($bundle));
    }

    /**
     * @return bool
     */
    public function deleteLocalBundle(): bool
    {
        return $this->delete(base_path('lasso-bundle.json'));
    }

    /**
     * Delete Lasso's base directory (.lasso)
     *
     * @return bool
     */
    public function deleteBaseLassoDirectory(): bool
    {
        return $this->deleteDirectory('.lasso');
    }

    /**
     * @return string
     */
    public function getLassoEnvironment(): string
    {
        return $this->lassoEnvironment;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setLassoEnvironment(string $environment): self
    {
        $this->lassoEnvironment = $environment;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * @param string $publicPath
     * @return $this
     */
    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
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
}
