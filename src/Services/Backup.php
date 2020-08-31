<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Sammyjo20\Lasso\Exceptions\RestoreFailed;

final class Backup
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var
     */
    protected $status = 'no_backup';

    /**
     * Backup constructor.
     *
     * @param Filesystem $filesystem
     * @param string $directory
     */
    public function __construct(Filesystem $filesystem, string $destination)
    {
        $this->filesystem = $filesystem;
        $this->destination = $destination;
    }

    /**
     * @return bool
     * @throws RestoreFailed
     */
    public function restoreBackup(): bool
    {
        if (!$this->filesystem->exists($this->destination)) {
            throw RestoreFailed::because('Couldn\'t find backup file.');
        }

        $public_path = config('lasso.public_path');

        $this->filesystem->cleanDirectory($public_path);
        $this->filesystem->move($this->destination, $public_path);

        return true;
    }

    /**
     * Clone the entire public directory.
     *
     * @return bool
     */
    public function startBackup(): bool
    {
        $public_path = config('lasso.public_path');
        $backup_destination = $this->destination;

        $success = $this->filesystem->copyDirectory(
            $public_path,
            $backup_destination
        );

        if ($success) {
            $this->setStatus('success');
            return true;
        }

        return false;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
