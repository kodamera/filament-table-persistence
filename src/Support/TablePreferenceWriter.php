<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Kodamera\FilamentTablePersistence\Models\TablePreference;
use Kodamera\FilamentTablePersistence\TablePersistence;
use Livewire\Component;

/**
 * Reads the session entries Filament writes during user interaction and
 * mirrors them into the durable per-user preferences row. Only writes when
 * the session entry actually exists (so we don't overwrite stored prefs
 * with defaults on a request that never touched the table state).
 */
class TablePreferenceWriter
{
    public static function persistSessionToDatabase(Component $component): void
    {
        $user = TablePersistence::user();

        if (! $user instanceof Authenticatable) {
            return;
        }

        $tableIdentifier = TablePreferenceLoader::tableIdentifierFor($component);
        $columnsSessionKey = TablePreferenceLoader::columnsSessionKey($component);

        $patch = [];

        if ((bool) config('filament-table-persistence.features.columns', true)
            && session()->has($columnsSessionKey)
        ) {
            $patch['columns'] = session()->get($columnsSessionKey);
        }

        if ($patch === []) {
            return;
        }

        $model = self::resolveModel();
        $foreignKey = (string) config('filament-table-persistence.user_foreign_key', 'user_id');
        $userId = (string) $user->getAuthIdentifier();

        /** @var TablePreference|null $existing */
        $existing = $model::query()
            ->where($foreignKey, $userId)
            ->where('table_identifier', $tableIdentifier)
            ->first();

        $current = $existing->preferences ?? [];
        $merged = array_merge($current, $patch);

        if ($existing !== null && $merged === $current) {
            return;
        }

        $model::query()->updateOrCreate(
            [
                $foreignKey => $userId,
                'table_identifier' => $tableIdentifier,
            ],
            [
                'preferences' => $merged,
            ],
        );
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
