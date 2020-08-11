<?php

namespace Sammyjo20\Lasso\Factories;

class BundleFactory
{
    /**
     * @param string $bundle_id
     * @return false|string
     */
    public static function create(string $bundle_id)
    {
        return json_encode(['id' => $bundle_id]);
    }
}
