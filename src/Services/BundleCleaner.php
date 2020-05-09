<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;

class BundleCleaner
{
    /**
     * @var array
     */
    protected $forbidden_files = [
        '.htaccess',
        'index.php',
        'robots.txt'
    ];

    /**
     * @var array
     */
    protected $forbidden_directories = [];

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
    }

    /**
     * @return array|string[]
     */
    public function getForbiddenFiles(): array
    {
        return $this->forbidden_files;
    }

    /**
     * @return array|string[]
     */
    public function getForbiddenDirectories(): array
    {
        return $this->forbidden_directories;
    }

    public function execute()
    {
        $filesystem = new Filesystem();
        $path = base_path('.lasso/bundle');

        // Loop through files
        foreach($this->getForbiddenFiles() as $file) {
            if ($filesystem->exists($path . '/' . $file)) {
                $filesystem->delete($path . '/' . $file);
            }
        }

        // Loop through directories
        foreach($this->getForbiddenDirectories() as $directory) {
            if ($filesystem->exists($path . '/' . $directory)) {
                $filesystem->deleteDirectory($path . '/' . $directory);
            }
        }
    }
}
