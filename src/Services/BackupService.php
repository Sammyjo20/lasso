<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Exceptions\RestoreFailed;

final class BackupService
{
    /**
     * Lasso Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Backup path
     */
    protected string $backupPath;

    /**
     * Constructor.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Copy a directory to a given location.
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
     * Restore a backup
     *
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
     * Set a backup path
     */
    public function setBackupPath(string $path): self
    {
        $this->backupPath = $path;

        return $this;
    }
}
