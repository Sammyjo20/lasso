<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

test('can publish and pull assets with lasso', function () {
    File::delete('./lasso-bundle.json');
    File::ensureDirectoryExists('./tests/Fixtures/Public');
    File::ensureDirectoryExists('./tests/Fixtures/Cloud');
    File::ensureDirectoryExists('./tests/Fixtures/Local');
    File::ensureDirectoryExists('.lasso');

    File::cleanDirectory('./tests/Fixtures/Cloud');

    expect(File::exists('./lasso-bundle.json'))->toBeFalse();
    expect(File::isEmptyDirectory('./tests/Fixtures/Cloud'))->toBeTrue();

    Config::set('lasso', defaultConfig());

    // Kick off the publish which will compile the asset

    $this->artisan('lasso:publish');

    expect(File::exists('./lasso-bundle.json'))->toBeTrue();

    $bundleName = json_decode(File::get('./lasso-bundle.json'), true, 512, JSON_THROW_ON_ERROR)['file'];

    // Check the bundle exists

    expect(File::exists('./tests/Fixtures/Cloud/lasso/global/' . $bundleName))->toBeTrue();

    File::cleanDirectory('./tests/Fixtures/Public');

    expect(File::isEmptyDirectory('./tests/Fixtures/Public'))->toBeTrue();

    $this->artisan('lasso:pull');

    // Now we'll ensure that the public directory has contents!

    expect(File::isEmptyDirectory('./tests/Fixtures/Public'))->toBeFalse();
    expect(File::exists('./tests/Fixtures/Public/app.css'))->toBeTrue();
    expect(File::exists('./tests/Fixtures/Public/lasso-logo.png'))->toBeTrue();
});
