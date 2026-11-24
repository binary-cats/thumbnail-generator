<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Uc\ThumbnailGenerator\ThumbnailGeneratorServiceProvider;

class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        $app->setBasePath(dirname(__DIR__));
        (new Filesystem)->ensureDirectoryExists($app->bootstrapPath('cache'));

        return [
            ThumbnailGeneratorServiceProvider::class,
        ];
    }
}
