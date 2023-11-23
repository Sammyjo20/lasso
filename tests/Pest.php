<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Sammyjo20\Lasso\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function sourceDirectory(): string
{
    return __DIR__ . '/Unit/Support/Zip/Source';
}

function destinationDirectory(): string
{
    return __DIR__ . '/Unit/Support/Zip/Destination';
}
