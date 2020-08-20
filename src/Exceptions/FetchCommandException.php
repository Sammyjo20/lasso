<?php

namespace Sammyjo20\Lasso\Exceptions;

class FetchCommandException extends \Exception
{
    /**
     * @param string $reason
     * @return static
     */
    public static function because(string $reason)
    {
        return new static(sprintf(
            'Failed to fetch the latest Lasso deployment. Reason: %s', $reason
        ));
    }
}
