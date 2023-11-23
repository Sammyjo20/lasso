<?php

declare(strict_types=1);

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
        $cloudDisk = config('lasso.storage.disk', 'assets');
        $publicPath = config('lasso.public_path', public_path());

        $this->setLassoEnvironment($lassoEnvironment)
            ->setCloudDisk($cloudDisk)
            ->setPublicPath($publicPath);
    }

    
    public function putStream($resource, string $destination): bool
    {
        $stream = fopen($destination, 'w+b');

        if (! $stream || stream_copy_to_stream($resource, $stream) === false || ! fclose($stream)) {
            return false;
        }

        return true;
    }

    
    public function createFreshLocalBundle(array $bundle): void
    {
        $this->deleteLocalBundle();

        $this->put(base_path('lasso-bundle.json'), json_encode($bundle));
    }

    
    public function deleteLocalBundle(): bool
    {
        return $this->delete(base_path('lasso-bundle.json'));
    }

    /**
     * Delete Lasso's base directory (.lasso)
     */
    public function deleteBaseLassoDirectory(): bool
    {
        return $this->deleteDirectory('.lasso');
    }

    
    public function getLassoEnvironment(): string
    {
        return $this->lassoEnvironment;
    }

    /**
     * @return $this
     */
    public function setLassoEnvironment(string $environment): self
    {
        $this->lassoEnvironment = $environment;

        return $this;
    }

    
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * @return $this
     */
    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    
    public function getCloudDisk(): string
    {
        return $this->cloudDisk;
    }

    /**
     * @return $this
     */
    public function setCloudDisk(string $disk): self
    {
        $this->cloudDisk = $disk;

        return $this;
    }
}
