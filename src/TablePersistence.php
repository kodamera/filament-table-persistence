<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Throwable;

/**
 * Static facade holding the global table-persistence configuration.
 *
 * Used by the Livewire hook to decide whether to apply for a given component,
 * and by the Filament Plugin wrapper as the underlying configuration store.
 */
class TablePersistence
{
    protected static bool $disabled = false;

    /** @var list<class-string> */
    protected static array $only = [];

    /** @var list<class-string> */
    protected static array $except = [];

    /** @var list<string> */
    protected static array $registeredPanels = [];

    public static function configure(Closure $callback): void
    {
        $callback(new self);
    }

    public function disabled(bool $disabled = true): self
    {
        self::$disabled = $disabled;

        return $this;
    }

    /** @param  list<class-string>  $components */
    public function only(array $components): self
    {
        self::$only = $components;

        return $this;
    }

    /** @param  list<class-string>  $components */
    public function except(array $components): self
    {
        self::$except = $components;

        return $this;
    }

    public static function registerPanel(string $panelId): void
    {
        if (! in_array($panelId, self::$registeredPanels, true)) {
            self::$registeredPanels[] = $panelId;
        }
    }

    public static function appliesTo(Component $component): bool
    {
        if (self::$disabled) {
            return false;
        }

        if (! (bool) config('filament-table-persistence.enabled', true)) {
            return false;
        }

        if (! self::currentPanelOptedIn()) {
            return false;
        }

        $class = $component::class;

        if (self::$only !== [] && ! in_array($class, self::$only, true)) {
            return false;
        }

        return ! in_array($class, self::$except, true);
    }

    public static function reset(): void
    {
        self::$disabled = false;
        self::$only = [];
        self::$except = [];
        self::$registeredPanels = [];
    }

    /**
     * When running inside a Filament panel, the panel must have registered the
     * plugin to opt in. When there is no active panel (filament/tables-only or
     * non-panel Livewire apps), this check is a no-op.
     */
    protected static function currentPanelOptedIn(): bool
    {
        if (! class_exists(Filament::class)) {
            return true;
        }

        try {
            $panel = Filament::getCurrentPanel();
        } catch (Throwable) {
            return true;
        }

        if ($panel === null) {
            return true;
        }

        return in_array($panel->getId(), self::$registeredPanels, true);
    }

    public static function user(): ?Authenticatable
    {
        if (class_exists(Filament::class)) {
            $user = Filament::auth()->user();

            if ($user !== null) {
                return $user;
            }
        }

        return Auth::user();
    }
}
