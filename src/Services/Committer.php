<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Sammyjo20\Lasso\Exceptions\CommitFailedException;
use Sammyjo20\Lasso\Helpers\CommandHelper;

class Committer
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Committer constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws CommitFailedException
     */
    public function commitAndPushBundle()
    {
        $path = base_path('lasso-bundle.json');

        if (!$this->filesystem->exists($path)) {
            throw new CommitFailedException('The "lasso-bundle.json" could not be found.');
        }

        $command = "git add 'lasso-bundle.json' && git commit -m'Lasso Assets 🐎' --author='Lasso <>' && git push";

        // Todo: Write a much better implementation of this.

        CommandHelper::run(`{$command}`, function () {
            //
        });
    }
}
