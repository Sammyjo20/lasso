<?php

namespace Sammyjo20\Lasso\Tasks\Publish;

use Illuminate\Support\Str;
use Sammyjo20\Lasso\Tasks\BaseJob;

final class PublishJob extends BaseJob
{
    /**
     * @var string
     */
    protected $bundleId;

    /**
     * PublishJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->generateBundleId()
            ->deleteLassoDirectory();
    }

    public function run(): void
    {
        $this->artisan->note('⏳ Compiling assets...');

        (new CompilationJob())
            ->setScript(config('lasso.compiler.script'))
            ->setTimeout(config('lasso.compiler.timeout'))
            ->run();

        $this->artisan->note('✅ Compiled assets.')
            ->note('⏳ Copying and zipping compiled assets...');

        (new BundleJob())
            ->setBundleId($this->bundleId)
            ->run();

        $this->artisan->note('✅ Successfully copied and zipped assets!');

        // Todo: Create Lasso Bundle file (lasso-bundle.json)
        // Todo: Upload Zip to filesystem
    }

    /**
     * @return $this
     */
    private function generateBundleId(): self
    {
        $this->bundleId = Str::random(20);

        return $this;
    }

    /**
     * @return $this
     */
    private function deleteLassoDirectory(): self
    {
        $this->filesystem->deleteBaseLassoDirectory();

        return $this;
    }
}
