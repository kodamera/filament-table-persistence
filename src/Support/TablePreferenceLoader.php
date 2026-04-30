<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Kodamera\FilamentTablePersistence\Models\TablePreference;
use Kodamera\FilamentTablePersistence\TablePersistence;
use Livewire\Component;

/**
 * Loads stored preferences from the database into the session entries Filament
 * already reads on boot. By writing into the same session keys Filament uses,
 * we avoid overriding any vendor methods.
 */
class TablePreferenceLoader
{
    public static function hydrateSessionFromDatabase(Component $component): void
    {
        $user = TablePersistence::user();

        if (!$user instanceof Authenticatable) {
            return;
        }

        $tableIdentifier = self::tableIdentifierFor($component);
        $columnsSessionKey = self::columnsSessionKey($component);

        $model = self::resolveModel();
        $foreignKey = (string) config('filament-table-persistence.user_foreign_key', 'user_id');

        $preference = $model::query()
            ->where($foreignKey, (string) $user->getAuthIdentifier())
            ->where('table_identifier', $tableIdentifier)
            ->first();

        /** @var array<string, mixed> $preferences */
        $preferences = $preference?->preferences ?? [];

        if ((bool) config('filament-table-persistence.features.columns', true)) {
            if (isset($preferences['columns']) && is_array($preferences['columns'])) {
                session()->put($columnsSessionKey, $preferences['columns']);
            } else {
                // No stored columns for this user — clear any stale session
                // entry left over from a previous user (e.g., impersonation)
                // so Filament falls back to defaults.
                session()->forget($columnsSessionKey);
            }
        }
    }

    public static function tableIdentifierFor(Component $component): string
    {
        return md5($component::class);
    }

    public static function columnsSessionKey(Component $component): string
    {
        return 'tables.'.md5($component::class).'_columns';
    }

    /** @return class-string<Model> */
    protected static function resolveModel(): string
    {
        /** @var class-string<Model> $model */
        $model = (string) config(
            'filament-table-persistence.model',
            TablePreference::class,
        );

        return $model;
    }
}
