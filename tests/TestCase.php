<?php

namespace Tests;

use MartinPL\BladeDirectivesNesting\PackageServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [PackageServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.cache', false);
    }
}
