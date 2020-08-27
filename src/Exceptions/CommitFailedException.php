<?php

namespace Sammyjo20\Lasso\Exceptions;

class CommitFailedException extends \Exception
{
    /**
     * @param string $reason
     * @return static
     */
    public static function because(string $reason)
    {
        return new static(sprintf(
            'Failed to push to git remote. Reason: %s', $reason
        ));
    }
}
