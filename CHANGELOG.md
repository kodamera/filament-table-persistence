# Changelog

All notable changes to `kodamera/filament-table-persistence` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] — 2026-04-30

Added
- Per-user, per-table column-visibility persistence backed by a `filament_table_preferences` table (one row per `(user_id, table_identifier)`)
- Forward-compatible JSON `preferences` schema (room for filters, sort, pagination, presets without future migrations)
- Per-panel opt-in via `TablePersistencePlugin::make()` in `$panel->plugins([...])` — multi-panel apps can opt panels in or out independently
- Non-panel configuration via static `TablePersistence::configure(fn ($p) => ...)` for `filament/tables`-only or plain Livewire apps
- `->only([...])` allow-list, `->except([...])` deny-list, `->disabled()` runtime kill-switch
- `PersistsTableState` trait for opt-in per-page activation as an alternative to global apply
- Filament v4 and v5 compatibility
- Laravel v11 and v12 support, PHP 8.2+
- GitHub Actions CI matrix: PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12, running Pint, PHPStan, Rector (dry-run), and Pest
- `composer audit` CI job to fail builds on known dependency advisories
- Dependabot config for weekly grouped composer + GitHub Actions updates (minor/patch only, majors ignored)
- Pest test suite
