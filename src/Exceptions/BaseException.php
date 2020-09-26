<?php

namespace Sammyjo20\Lasso\Exceptions;

class BaseException extends \Exception
{
    /**
     * @var string
     */
    public static $event = 'An exception was thrown.';

    /**
     * @param string $reason
     * @return static
     */
    public static function because(string $reason)
    {
        return new static(sprintf(
            '%s Reason: %s',
            static::$event,
            $reason
        ));
    }
}
