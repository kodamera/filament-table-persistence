<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Livewire;

use Filament\Tables\Contracts\HasTable;
use Kodamera\FilamentTablePersistence\Support\TablePreferenceLoader;
use Kodamera\FilamentTablePersistence\Support\TablePreferenceWriter;
use Kodamera\FilamentTablePersistence\TablePersistence;
use Livewire\Component;
use Livewire\ComponentHook;

/**
 * Bridges the Filament table session state and the per-user preferences row.
 *
 * - mount/hydrate: pulls stored preferences from the DB and seeds the session
 *   entries Filament reads when its table boots.
 * - dehydrate: mirrors any session writes Filament made during the request
 *   back into the DB row.
 */
class TablePersistenceHook extends ComponentHook
{
    public function mount(): void
    {
        $this->hydrateFromDatabase();
    }

    public function hydrate(): void
    {
        $this->hydrateFromDatabase();
    }

    public function dehydrate(): void
    {
        $component = $this->resolveApplicableComponent();

        if (!$component instanceof Component) {
            return;
        }

        TablePreferenceWriter::persistSessionToDatabase($component);
    }

    protected function hydrateFromDatabase(): void
    {
        $component = $this->resolveApplicableComponent();

        if (!$component instanceof Component) {
            return;
        }

        TablePreferenceLoader::hydrateSessionFromDatabase($component);
    }

    protected function resolveApplicableComponent(): ?Component
    {
        $component = $this->component;

        if (! $component instanceof Component) {
            return null;
        }

        if (! $component instanceof HasTable) {
            return null;
        }

        return TablePersistence::appliesTo($component) ? $component : null;
    }
}
