<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use \Illuminate\Filesystem\Filesystem as BaseFilesystem;

/**
 * @internal
 */
class Filesystem extends BaseFilesystem
{
    /**
     * Lasso Environment
     */
    protected string $lassoEnvironment;

    /**
     * Cloud Disk Path
     */
    protected string $cloudDisk;

    /**
     * Public Path
     */
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

    /**
     * Store a file as a stream
     *
     * @param resource $resource
     */
    public function putStream(mixed $resource, string $destination): bool
    {
        $stream = fopen($destination, 'wb+');

        if (! $stream || stream_copy_to_stream($resource, $stream) === false || ! fclose($stream)) {
            return false;
        }

        return true;
    }

    /**
     * Create fresh from local bundle
     *
     * @param array<mixed, mixed> $bundle
     */
    public function createFreshLocalBundle(array $bundle): void
    {
        $this->deleteLocalBundle();

        $this->put(base_path('lasso-bundle.json'), (string)json_encode($bundle));
    }

    /**
     * Delete local bundle
     */
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
     * Set the Lasso environment
     */
    public function setLassoEnvironment(string $environment): self
    {
        $this->lassoEnvironment = $environment;

        return $this;
    }

    /**
     * Get the Lasso environment
     */
    public function getLassoEnvironment(): string
    {
        return $this->lassoEnvironment;
    }

    /**
     * Get the public path
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * Get the cloud disk
     */
    public function getCloudDisk(): string
    {
        return $this->cloudDisk;
    }
}
