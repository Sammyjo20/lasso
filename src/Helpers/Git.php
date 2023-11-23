<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use Exception;
use Sammyjo20\Lasso\Exceptions\GitHashException;

class Git
{
    /**
     * Get the current commit hash
     *
     * @throws GitHashException
     */
    public static function getCommitHash(): ? string
    {
        try {
            $branch = str_replace('\n', '', last(explode('/', file_get_contents(base_path() . '/.git/HEAD'))));
            $hash = file_get_contents(base_path() . '/.git/refs/heads/' . $branch);
        } catch (Exception $exception) {
            throw new GitHashException($exception->getMessage(), previous: $exception);
        }

        return $hash ? mb_substr($hash, 0, 12) : null;
    }
}
