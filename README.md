# Filament Table Persistence

Persist Filament table state to the database, per user, across sessions. Day-1 scope: column visibility. Forward-compatible schema for filters, sort, pagination, and presets.

Filament out of the box keeps column toggle state in the session — so it survives a page refresh, but not a logout, a session regeneration, or a different browser. This package mirrors that state into a `filament_table_preferences` row keyed by `(user_id, table_identifier)` so it survives all of the above.

## Requirements

- PHP `^8.2`
- Filament `^4.0` or `^5.0` (only `filament/tables` is required as a hard dep; `filament/filament` is suggested for the Plugin registration ergonomic)
- Laravel `^11.0` or `^12.0`

## Install

```bash
composer require kodamera/filament-table-persistence
php artisan migrate
```

The migration is auto-loaded and the Livewire hook is auto-registered, but persistence is **opt-in per panel** — register the plugin on each panel that should persist table state.

### Publishing the config or migration

Both are optional. Publish only what you need to customize:

```bash
# Override defaults like the table name, model class, or user FK
php artisan vendor:publish --tag=filament-table-persistence-config

# Take ownership of the migration (e.g., to add a tenant_id column)
php artisan vendor:publish --tag=filament-table-persistence-migrations
```

After publishing the config you'll have `config/filament-table-persistence.php`:

```php
return [
    'enabled' => true,
    'model' => \Kodamera\FilamentTablePersistence\Models\TablePreference::class,
    'table' => 'filament_table_preferences',
    'user_foreign_key' => 'user_id',
    'features' => [
        'columns' => true,
    ],
];
```

#### Examples

**Rename the table** (e.g., to namespace it under your app):

```php
'table' => 'app_table_preferences',
```

Then re-run `php artisan migrate` after editing the published migration to use the new name.

**Use a UUID/ULID `users` table** — the column is already a `string`, so no schema change needed; the package casts the auth identifier to a string when querying.

**Swap the model** (to add casts, scopes, or a tenant relationship):

```php
'model' => \App\Models\TablePreference::class,
```

Your model should extend `Kodamera\FilamentTablePersistence\Models\TablePreference` (or replicate its `preferences` array cast and dynamic table name).

**Change the user foreign key column** (e.g., your users table uses `member_id`):

```php
'user_foreign_key' => 'member_id',
```

Make sure the published migration uses the same column name.

**Disable globally at runtime** (e.g., during a deploy or for a test environment):

```php
'enabled' => env('TABLE_PERSISTENCE_ENABLED', true),
```

**Turn off column persistence** while keeping the package wired up for future features:

```php
'features' => [
    'columns' => false,
],
```

## Configuration

### With a Filament panel (`filament/filament` installed)

Add the plugin to every panel that should persist table state. Panels without it are unaffected, so panels in the same app can opt out simply by not registering the plugin.

```php
use Kodamera\FilamentTablePersistence\TablePersistencePlugin;

$panel->plugins([
    TablePersistencePlugin::make()
        ->except([\App\Filament\Resources\AuditLog\Pages\ListAuditLogs::class]),
]);
```

### Without a panel (or in non-Filament Livewire apps using `filament/tables`)

When there is no active Filament panel context, the panel opt-in check is skipped and configuration is global:

```php
use Kodamera\FilamentTablePersistence\TablePersistence;

// In AppServiceProvider::boot()
TablePersistence::configure(
    fn (TablePersistence $p) => $p->except([\App\Livewire\Reports::class])
);
```

### Available options

| Method | Effect |
|---|---|
| `->disabled()` | Runtime kill-switch (e.g., during tests) |
| `->only([…])` | Allow-list of Livewire component classes |
| `->except([…])` | Deny-list of Livewire component classes |

`only` takes precedence over `except`. If both are empty, all Filament tables are persisted.

## Per-page opt-in (alternative to global)

For non-panel apps that want to disable the global apply and opt in per page, add the trait to specific pages:

```php
TablePersistence::configure(fn ($p) => $p->disabled());

class ListUsers extends \Filament\Resources\Pages\ListRecords
{
    use \Kodamera\FilamentTablePersistence\Concerns\PersistsTableState;
}
```

## How it works

Filament's table column manager reads/writes `session('tables.{md5(class)}_columns')`. This package:

1. On Livewire `mount`/`hydrate`, loads the row from `filament_table_preferences` and seeds the same session key.
2. On Livewire `dehydrate`, mirrors any changes back to the database.

We never override Filament internals. The session is the fast path; the DB is the durable backing store.

## Schema

Single JSON `preferences` column so future features (filters, sort, per-page, search, presets) slot in without migrations:

```php
Schema::create('filament_table_preferences', function (Blueprint $t) {
    $t->id();
    $t->string('user_id')->index();          // string handles bigint, UUID, ULID
    $t->string('table_identifier');           // matches Filament's md5(class) key
    $t->json('preferences');                  // { columns: [...], filters: {...}, ... }
    $t->timestamps();
    $t->unique(['user_id', 'table_identifier']);
});
```

`user_id` is stored as a string to support bigint, UUID, and ULID auth identifiers without configuration. Auth resolution prefers `Filament::auth()->user()` so panel-specific guards work; falls back to `Auth::user()`.

## Multi-tenancy

Tenant scoping is not built in. Extension path: add a nullable `tenant_id` column in your own migration and a `tenant_foreign_key` config; the unique key becomes `(user_id, tenant_id, table_identifier)`. We'll add first-class support in a future minor release.

## Roadmap

- Filter persistence
- Sort persistence
- Search persistence
- Per-page (pagination size) persistence
- Reset action UI
- Shared / role-based presets
- Cache layer (Redis/Memcached) as the fast path

## Development

```bash
composer install
composer test       # Pest
composer format     # Pint
composer analyse    # Larastan
composer rector     # Rector --dry-run
composer ci         # all of the above
```

## License

MIT — see `LICENSE.md`.
