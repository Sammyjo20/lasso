<?php

namespace Sammyjo20\Lasso\Exceptions;

class ConfigFailedValidation extends BaseException
{
    /**
     * @var string
     */
    public static $event = 'Failed to parse configuration.';
}
