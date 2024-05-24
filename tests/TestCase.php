<?php

namespace Mchev\Banhammer\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        // Load the .env file
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            'Mchev\Banhammer\BanhammerServiceProvider',
        ];
    }

    /**
     * Run package database migrations.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--path' => realpath(__DIR__.'/../database/migrations'),
        ]);
    }

}