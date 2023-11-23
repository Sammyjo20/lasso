<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Exceptions;

class BaseException extends \Exception
{
    /**
     * @var string
     */
    public static $event = 'An exception was thrown.';

    /**
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
