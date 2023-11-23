<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

class Bundle
{
    /**
     * @var string
     */
    protected $bundleId;

    /**
     * @var string
     */
    protected $zipPath;

    
    public function create(): array
    {
        // Now we will generate a checksum for the file. This is useful
        // to do, just in case something goes wrong in uploading the file
        // and also to check against the download later on.

        $checksum = BundleIntegrityHelper::generateChecksum($this->zipPath);

        return ['file' => $this->bundleId . '.zip', 'checksum' => $checksum];
    }

    /**
     * @return $this
     */
    public function setBundleId(string $bundleId): self
    {
        $this->bundleId = $bundleId;

        return $this;
    }

    /**
     * @return $this
     */
    public function setZipPath(string $path): self
    {
        $this->zipPath = $path;

        return $this;
    }
}
