<?php

namespace Sammyjo20\Lasso\Helpers;

use Sammyjo20\Lasso\Exceptions\GitHashException;

class Git
{
    /**
     * @return string|null
     * @throws GitHashException
     */
    public static function getCommitHash():? string
    {
        try {
            $hash = file_get_contents(base_path() . '/.git/HEAD');
        } catch (\Exception $exception) {
            throw new GitHashException($exception->getMessage(), $exception);
        }

        if ($hash) {
            return substr($hash, 0, 12);
        }

        return null;
    }
}
