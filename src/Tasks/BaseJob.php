<?php

namespace Sammyjo20\Lasso\Tasks;

use Sammyjo20\Lasso\Helpers\Cloud;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Interfaces\JobInterface;

abstract class BaseJob implements JobInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Cloud
     */
    protected $cloud;

    /**
     * @var Artisan
     */
    protected $artisan;

    /**
     * BaseJob constructor.
     */
    public function __construct()
    {
        $this->setArtisan()
            ->setFilesystem();

        // The Cloud class should be defined after the Filesystem as
        // it depends on the Filesystem.

        $this->setCloud();
    }

    /**
     * @return $this
     */
    private function setArtisan(): self
    {
        $this->artisan = resolve(Artisan::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function setFilesystem(): self
    {
        $this->filesystem = resolve(Filesystem::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function setCloud(): self
    {
        $this->cloud = new Cloud;

        return $this;
    }

    /**
     * @return array
     */
    protected function filterWebhooksToRun(array $webhooks): array
    {
        $lassoEnvironment = config('lasso.storage.environment', null);

        return array_key_exists($lassoEnvironment, $webhooks) ?
            array_merge($webhooks[$lassoEnvironment], $this->getAlwaysRunWebhooks($webhooks)) :
            $this->getAlwaysRunWebhooks($webhooks);
    }

    /**
     * @return array
     */
    protected function getAlwaysRunWebhooks(array $webhooks): array
    {        
        return array_key_exists('always', $webhooks) ?
            array_merge($webhooks['always'], $this->numericallyIndexedWebhooks($webhooks)) :
            $this->numericallyIndexedWebhooks($webhooks);
    }

    /**
     * @return array
     */
    private function numericallyIndexedWebhooks($webhooks): array
    {
        return array_filter(array_values($webhooks), 'is_string');
    }
}
