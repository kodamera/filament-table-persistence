<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence;

use Illuminate\Support\ServiceProvider;
use Kodamera\FilamentTablePersistence\Livewire\TablePersistenceHook;
use Livewire\LivewireManager;

class TablePersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-table-persistence.php',
            'filament-table-persistence',
        );

        // Register the Livewire component hook as soon as the LivewireManager
        // is resolved. Livewire's own service provider boots its internal
        // hooks and then calls ComponentHookRegistry::boot(), which freezes
        // the mount/hydrate listeners for whatever hooks are registered at
        // that moment. Registering during resolution guarantees we land in
        // that set regardless of provider boot order.
        $this->app->resolving(LivewireManager::class, function (LivewireManager $livewire): void {
            $livewire->componentHook(TablePersistenceHook::class);
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/filament-table-persistence.php' => config_path('filament-table-persistence.php'),
        ], 'filament-table-persistence-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-table-persistence-migrations');
    }
}
