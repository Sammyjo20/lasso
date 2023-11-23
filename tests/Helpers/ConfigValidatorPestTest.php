<?php

use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Exceptions\ConfigFailedValidation;

beforeEach(function () {
    config()->set([
        'filesystems.disks' => [
            'assets' => [
                'driver' => 's3',
            ],
        ],
    ]);
});

test('it throws exception when storage push to git is set', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/push_to_git/');

    config()->set(['lasso.storage.push_to_git' => true]);

    (new ConfigValidator)->validate();
});

test('it throws an exception when the compile script is not set', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/npm run production/');

    config()->set(['lasso.compiler.script' => null]);

    (new ConfigValidator)->validate();
});

test('it throws an exception when an invalid compiler output is provided', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessage('You must specify a valid output setting. Available options: all, progress, disable.');

    config()->set(['lasso.compiler.output' => 'abc']);

    (new ConfigValidator)->validate();
});

test('it throws exception when disk does not exist', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/not a valid disk/');

    config()->set(['filesystems.disks' => null]);

    (new ConfigValidator)->validate();
});

test('it throws exception when bundle count is missing', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/how many bundles/');

    config()->set(['lasso.storage.max_bundles' => null]);

    (new ConfigValidator)->validate();
});

test('it throws exception when bundle count is less than one', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/how many bundles/');

    config()->set(['lasso.storage.max_bundles' => 0]);

    (new ConfigValidator)->validate();
});

test('it throws exception when public path is inaccessible', function () {
    $this->expectException(ConfigFailedValidation::class);
    $this->expectExceptionMessageMatches('/accessible directory/');

    config()->set(['lasso.public_path' => 'a_non_existing_path']);

    (new ConfigValidator)->validate();
});
