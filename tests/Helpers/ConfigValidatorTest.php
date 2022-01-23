<?php

namespace Sammyjo20\Lasso\Tests\Helpers;

use Sammyjo20\Lasso\Exceptions\ConfigFailedValidation;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Tests\TestCase;

class ConfigValidatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        config()->set([
            'filesystems.disks' => [
                'assets' => [
                    "driver" => "s3",
                ],
            ],
        ]);
    }

    /** @test */
    public function it_throws_exception_when_storage_push_to_git_is_set()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/push_to_git/');
        config()->set(['lasso.storage.push_to_git' => true]);
        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_compile_script_is_not_set()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/npm run production/');

        config()->set(['lasso.compiler.script' => null]);

        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_invalid_compiler_output_is_provided()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessage('You must specify a valid output setting. Available options: all, progress, disable.');

        config()->set(['lasso.compiler.output' => 'abc']);

        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_disk_does_not_exist()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/not a valid disk/');

        config()->set(['filesystems.disks' => null]);

        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_bundle_count_is_missing()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/how many bundles/');

        config()->set(['lasso.storage.max_bundles' => null]);

        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_bundle_count_is_less_than_one()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/how many bundles/');

        config()->set(['lasso.storage.max_bundles' => 0]);

        (new ConfigValidator())->validate();
    }

    /** @test */
    public function it_throws_exception_when_public_path_is_inaccessible()
    {
        $this->expectException(ConfigFailedValidation::class);
        $this->expectExceptionMessageMatches('/accessible directory/');

        config()->set(['lasso.public_path' => 'a_non_existing_path']);

        (new ConfigValidator())->validate();
    }
}
