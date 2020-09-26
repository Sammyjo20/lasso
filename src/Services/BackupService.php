<?php

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Exceptions\RestoreFailed;
use Sammyjo20\Lasso\Helpers\Filesystem;

final class BackupService
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var bool
     */
    protected $backupPath;

    /**
     * Backup constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->setFilesystem($filesystem);
    }

    /**
     * Copy a directory to a given location.
     *
     * @param string $sourceDirectory
     * @param string $destinationDirectory
     * @return bool
     */
    public function createBackup(string $sourceDirectory, string $destinationDirectory): bool
    {
        $success = $this->filesystem
            ->copyDirectory($sourceDirectory, $destinationDirectory);

        if ($success) {
            $this->setBackupPath($destinationDirectory);

            return true;
        }

        return false;
    }

    /**
     * @param string $destinationDirectory
     * @return bool
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    public function restoreBackup(string $destinationDirectory): bool
    {
        if (! $this->filesystem->exists($this->backupPath)) {
            throw RestoreFailed::because('Couldn\'t find backup directory.');
        }

        $this->filesystem->cleanDirectory($destinationDirectory);

        $this->filesystem->move($this->backupPath, $destinationDirectory);

        return true;
    }

    /**
     * @param Filesystem $filesystem
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): self
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasBackup(): bool
    {
        return ! is_null($this->backupPath);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setBackupPath(string $path): self
    {
        $this->backupPath = $path;

        return $this;
    }
}
