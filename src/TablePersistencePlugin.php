<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Filament panel plugin wrapper. Delegates configuration to the static
 * {@see TablePersistence} facade so the package works without filament/filament
 * (just configure the facade directly).
 *
 * Only loaded when the host application has filament/filament installed —
 * the parent interface is resolved on instantiation, which only happens in
 * a panel provider.
 */
class TablePersistencePlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'table-persistence';
    }

    public function disabled(bool $disabled = true): self
    {
        TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->disabled($disabled));

        return $this;
    }

    /** @param  list<class-string>  $components */
    public function only(array $components): self
    {
        TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->only($components));

        return $this;
    }

    /** @param  list<class-string>  $components */
    public function except(array $components): self
    {
        TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->except($components));

        return $this;
    }

    public function register(Panel $panel): void
    {
        TablePersistence::registerPanel($panel->getId());
    }

    public function boot(Panel $panel): void {}
}
