<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Tasks\Publish;

use Sammyjo20\Lasso\Tasks\BaseJob;
use Sammyjo20\Lasso\Helpers\FileLister;
use Sammyjo20\Lasso\Services\ArchiveService;

final class BundleJob extends BaseJob
{
    /**
     * @var string
     */
    protected string $bundleId;

    /**
     * @var array
     */
    protected array $forbiddenFiles = [
        '.htaccess',
        'web.config',
        'index.php',
        'robots.txt',
        'storage',
    ];

    /**
     * @var array
     */
    protected array $forbiddenDirectories = [
        'storage',
        'hot',
    ];

    /**
     * BundleJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $userForbiddenFiles = config('lasso.compiler.excluded_files', []);
        $userForbiddenDirs = config('lasso.compiler.excluded_directories', []);

        $this->forbiddenFiles = array_merge($this->forbiddenFiles, $userForbiddenFiles);
        $this->forbiddenDirectories = array_merge($this->forbiddenDirectories, $userForbiddenDirs);
    }

    /**
     * Run the bundle job
     *
     * @return void
     */
    public function run(): void
    {
        $filesystem = $this->filesystem;

        // Let's create a finder instance
        $files = (new FileLister($filesystem->getPublicPath()))
            ->getFinder();

        $workingPath = base_path('.lasso/bundle');

        // Now we want to iterate through each of Finder's
        // files. We are doing this because Finder will automatically
        // ignore unreadable directories, VCS files, and include dot-files.

        foreach ($files as $file) {
            // Make sure it's not an excluded file.
            if (in_array($file->getFilename(), $this->forbiddenFiles, true)) {
                continue;
            }

            // Make sure the file exists and is readable.
            if (! $filesystem->exists($file->getPathname()) || ! $filesystem->isReadable($file->getPathname())) {
                continue;
            }

            // Make sure the destination exists
            $filesystem->ensureDirectoryExists(
                $workingPath . '/' . $file->getRelativePath()
            );

            // Copy the file!

            $filesystem->copy(
                $file->getPathname(),
                $workingPath . '/' . $file->getRelativePathname()
            );
        }

        // Loop through directories and delete any "excluded"
        foreach ($this->forbiddenDirectories as $directory) {
            if ($filesystem->exists($workingPath . '/' . $directory)) {
                $filesystem->deleteDirectory($workingPath . '/' . $directory);
            }
        }

        // Now let's Zip up all our files...

        $this->filesystem->ensureDirectoryExists('.lasso/dist');
        $destinationPath = base_path('.lasso/dist/' . $this->bundleId . '.zip');

        ArchiveService::create($workingPath, $destinationPath);
    }

    /**
     * Set the Bundle ID
     */
    public function setBundleId(string $bundleId): self
    {
        $this->bundleId = $bundleId;

        return $this;
    }
}
