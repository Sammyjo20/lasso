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
    protected $publicPath;

    /**
     * Filesystem constructor.
     */
    public function __construct()
    {
        $lassoEnvironment = config('lasso.storage.environment') ?? 'global';
        $publicPath = config('lasso.public_path');

        $this->setLassoEnvironment($lassoEnvironment)
            ->setPublicPath($publicPath);
    }

    /**
     * Returns the Lasso upload directory. You can specify a file
     * to create a fully qualified URL.
     *
     * @param string|null $file
     * @param string|null $environment
     * @return string
     */
    public function getLassoPath(string $file = null, string $environment = null): string
    {
        $uploadPath = config('lasso.storage.upload_to');

        $dir = sprintf('%s/%s', $uploadPath, $environment);

        if (is_null($file)) {
            return $dir;
        }

        return $dir . '/' . ltrim($file, '/');
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
}
