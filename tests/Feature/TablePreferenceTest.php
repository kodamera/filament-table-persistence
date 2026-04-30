<?php

declare(strict_types=1);

use Illuminate\Database\UniqueConstraintViolationException;
use Kodamera\FilamentTablePersistence\Models\TablePreference;

it('persists and reads preferences for a user/table pair', function (): void {
    TablePreference::query()->create([
        'user_id' => '1',
        'table_identifier' => md5('App\\Filament\\ListUsers'),
        'preferences' => ['columns' => ['name' => true, 'email' => false]],
    ]);

    $row = TablePreference::query()->firstOrFail();

    expect($row->preferences)->toBe(['columns' => ['name' => true, 'email' => false]]);
});

it('enforces the unique constraint on user_id + table_identifier', function (): void {
    $payload = [
        'user_id' => '1',
        'table_identifier' => md5('App\\Filament\\ListUsers'),
        'preferences' => ['columns' => []],
    ];

    TablePreference::query()->create($payload);

    expect(fn () => TablePreference::query()->create($payload))
        ->toThrow(UniqueConstraintViolationException::class);
});
