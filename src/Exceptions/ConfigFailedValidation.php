<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Exceptions;

class ConfigFailedValidation extends BaseException
{
    /**
     * Default Event
     */
    public static string $event = 'Failed to parse configuration.';
}
