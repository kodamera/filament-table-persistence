<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Tests;

use Kodamera\FilamentTablePersistence\TablePersistence;
use Kodamera\FilamentTablePersistence\TablePersistenceServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        TablePersistence::reset();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            TablePersistenceServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
