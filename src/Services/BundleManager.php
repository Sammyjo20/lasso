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
        $filesystem = $this->filesystem;

        // Let's create a finder instance
        $files = (new FileLister($public_path))
            ->getFinder();

        $path = base_path('.lasso/bundle');

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
                $path . '/' . $file->getRelativePath()
            );

            // Copy the file!

            $filesystem->copy(
                $file->getPathname(),
                $path . '/' . $file->getRelativePathname()
            );
        }

        // Loop through directories and delete any "excluded"
        foreach($this->getForbiddenDirectories() as $directory) {
            if ($filesystem->exists($path . '/' . $directory)) {
                $filesystem->deleteDirectory($path . '/' . $directory);
            }
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
