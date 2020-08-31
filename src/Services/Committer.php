<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Sammyjo20\Lasso\Exceptions\CommittingFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Committer
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
     * @param string $command
     */
    public function commit(string $command)
    {
        $process = new Process(explode(' ', $command));

        $process->setTimeout(60)
            ->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @throws CommittingFailed
     */
    public function commitAndPushBundle()
    {
        $path = base_path('lasso-bundle.json');

        if (!$this->filesystem->exists($path)) {
            throw new CommittingFailed('The "lasso-bundle.json" could not be found.');
        }

        $command = "git add 'lasso-bundle.json' && git commit -m'Lasso Assets 🐎' --author='Lasso <>' && git push";

        $this->commit(`{$command}`);
    }
}
