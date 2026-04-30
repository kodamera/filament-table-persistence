<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Concerns;

use Kodamera\FilamentTablePersistence\Support\TablePreferenceLoader;
use Kodamera\FilamentTablePersistence\Support\TablePreferenceWriter;

/**
 * Optional per-page opt-in. Mirrors the global Livewire hook for projects that
 * prefer explicit, per-component activation (e.g., when global apply is
 * disabled or scoped via except() to most pages).
 */
trait PersistsTableState
{
    public function bootedPersistsTableState(): void
    {
        TablePreferenceLoader::hydrateSessionFromDatabase($this);
    }

    public function dehydrate(): void
    {
        TablePreferenceWriter::persistSessionToDatabase($this);
    }
}
