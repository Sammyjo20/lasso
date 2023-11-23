<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Exceptions;

use Exception;
use Throwable;

class BaseException extends Exception
{
    /**
     * Default Event
     */
    public static string $event = 'An exception was thrown.';

    /**
     * Constructor
     */
    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create a new exception with a reason
     */
    public static function because(string $reason): self
    {
        return new static(sprintf('%s Reason: %s', static::$event, $reason));
    }
}
