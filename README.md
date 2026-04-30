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

## Usage

Register the plugin on every panel that should persist table state. Panels without it are unaffected, so panels in the same app can opt out simply by not registering the plugin.

```php
use Filament\Panel;
use Kodamera\FilamentTablePersistence\TablePersistencePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            TablePersistencePlugin::make(),
        ]);
}
```

Once registered, persistence is automatic: when a user toggles column visibility, the new state is written to `filament_table_preferences` and re-applied on every subsequent visit — across logouts, session resets, and different browsers. Each table is keyed by `(user_id, table_identifier)`.

To exclude specific pages:

```php
TablePersistencePlugin::make()
    ->except([\App\Filament\Resources\AuditLog\Pages\ListAuditLogs::class]),
```

## Configuration

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
