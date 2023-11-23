<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use \Illuminate\Filesystem\Filesystem as BaseFilesystem;

/**
 * @internal
 */
class Filesystem extends BaseFilesystem
{
    
    protected string $lassoEnvironment;

    
    protected string $cloudDisk;

    
    protected string $publicPath;

    /**
     * Filesystem constructor.
     */
    public function __construct()
    {
        $this->lassoEnvironment = config('lasso.storage.environment') ?? 'global';
        $this->cloudDisk = config('lasso.storage.disk', 'assets');
        $this->publicPath = config('lasso.public_path', public_path());
    }

    
    public function putStream($resource, string $destination): bool
    {
        $stream = fopen($destination, 'wb+');

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

    /**
     * @return $this
     */
    public function setLassoEnvironment(string $environment): self
    {
        $this->lassoEnvironment = $environment;

        return $this;
    }

    public function getLassoEnvironment(): string
    {
        return $this->lassoEnvironment;
    }

    
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    
    public function getCloudDisk(): string
    {
        return $this->cloudDisk;
    }
}
