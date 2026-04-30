<?php

declare(strict_types=1);

namespace Kodamera\FilamentTablePersistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $table_identifier
 * @property array<string, mixed> $preferences
 */
class TablePreference extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return (string) config('filament-table-persistence.table', 'filament_table_preferences');
    }

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
        ];
    }
}
