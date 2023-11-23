<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Exceptions\RestoreFailed;

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
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->setFilesystem($filesystem);
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
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): self
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    
    public function hasBackup(): bool
    {
        return ! is_null($this->backupPath);
    }

    /**
     * @return $this
     */
    public function setBackupPath(string $path): self
    {
        $this->backupPath = $path;

        return $this;
    }
}
