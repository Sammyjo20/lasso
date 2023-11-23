<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Exceptions;

class BaseException extends \Exception
{
    /**
     * Default Event
     */
    public static string $event = 'An exception was thrown.';

    /**
     * Create a new exception with a reason
     */
    public static function because(string $reason): self
    {
        return new static(sprintf('%s Reason: %s', static::$event, $reason));
    }
}
