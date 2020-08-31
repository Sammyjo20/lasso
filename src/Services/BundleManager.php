<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Sammyjo20\Lasso\Helpers\FileLister;

final class BundleManager
{
    /**
     * @var array
     */
    protected $forbidden_files = [
        '.htaccess',
        'index.php',
        'robots.txt',
        'storage'
    ];

    /**
     * @var array
     */
    protected $forbidden_directories = [
        'storage'
    ];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * BundleCleaner constructor.
     */
    public function __construct()
    {
        $this->forbidden_files = array_merge(
            $this->forbidden_files,
            config('lasso.compiler.excluded_files')
        );

        $this->forbidden_directories = array_merge(
            $this->forbidden_directories,
            config('lasso.compiler.excluded_directories')
        );

        $this->filesystem = new Filesystem();
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $public_path = config('lasso.public_path');

        // Firstly, let's move all the files into a temporary location.
        $this->filesystem->copyDirectory($public_path, '.lasso/bundle-unsafe');

        // Now we want to process each file, and make sure it's safe.
        $this->createSafeDirectory();
    }

    /**
     * @return void
     */
    private function createSafeDirectory(): void
    {
        $filesystem = $this->filesystem;
        $path = base_path('.lasso/bundle-unsafe');

        // Loop through files and delete any "excluded"
        foreach($this->getForbiddenFiles() as $file) {
            if ($filesystem->exists($path . '/' . $file)) {
                $filesystem->delete($path . '/' . $file);
            }
        }

        // Loop through directories and delete any "excluded"
        foreach($this->getForbiddenDirectories() as $directory) {
            if ($filesystem->exists($path . '/' . $directory)) {
                $filesystem->deleteDirectory($path . '/' . $directory);
            }
        }

        // Now let's create a finder instance from the files remaining.
        $files = (new FileLister($path))
            ->getFinder();

        $safe_path = base_path('.lasso/bundle-safe');

        // Now we want to iterate through each of Finder's
        // files. We are doing this because Finder will automatically
        // ignore unreadable directories, VCS files, and include dot-files.

        foreach($files as $file) {

            // Make sure it's not an excluded file.
            if (in_array($file->getFilename(), $this->getForbiddenFiles(), true)) {
                continue;
            }

            // Make sure the file exists and is readable.
            if (!$filesystem->exists($file->getPathname()) || !$filesystem->isReadable($file->getPathname())) {
                continue;
            }

            // Make sure the destination exists
            $filesystem->ensureDirectoryExists(
                $safe_path . '/' . $file->getRelativePath()
            );

            // Copy the file!

            $filesystem->copy(
                $file->getPathname(),
                $safe_path . '/' . $file->getRelativePathname()
            );
        }
    }

    /**
     * @return array|string[]
     */
    private function getForbiddenFiles(): array
    {
        return $this->forbidden_files;
    }

    /**
     * @return array|string[]
     */
    private function getForbiddenDirectories(): array
    {
        return $this->forbidden_directories;
    }
}
