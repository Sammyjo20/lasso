<?php

namespace Sammyjo20\Lasso\Exceptions;

class ConfigFailedValidation extends \Exception
{
    /**
     * @param string $reason
     * @return static
     */
    public static function because(string $reason)
    {
        return new static(sprintf(
            'Failed to process configuration. Reason: %s', $reason
        ));
    }
}
