<?php

declare(strict_types=1);
use Kodamera\FilamentTablePersistence\Models\TablePreference;

return [
    'enabled' => true,

    'model' => TablePreference::class,

    'table' => 'filament_table_preferences',

    'user_foreign_key' => 'user_id',

    'features' => [
        'columns' => true,
    ],
];
