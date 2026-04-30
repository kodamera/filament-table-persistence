<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('filament-table-persistence.table', 'filament_table_preferences');
        $userForeignKey = (string) config('filament-table-persistence.user_foreign_key', 'user_id');

        Schema::create($tableName, function (Blueprint $table) use ($userForeignKey): void {
            $table->id();
            $table->string($userForeignKey)->index();
            $table->string('table_identifier');
            $table->json('preferences');
            $table->timestamps();

            $table->unique([$userForeignKey, 'table_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            (string) config('filament-table-persistence.table', 'filament_table_preferences')
        );
    }
};
