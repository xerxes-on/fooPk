<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Ingredient\Enums\IngredientUnitType;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ingredient_units', static function (Blueprint $table) {
            $table->tinyInteger('type', unsigned: true)
                ->after('id')
                ->nullable(false)
                ->default(IngredientUnitType::PRIMARY->value);
        });

        Schema::table('ingredients', static function (Blueprint $table) {
            $table->unsignedInteger('alternative_unit_id')
                ->after('unit_id')
                ->nullable(true);

            $table->foreign('alternative_unit_id')
                ->references('id')
                ->on('ingredient_units')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_units', static function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('ingredients', static function (Blueprint $table) {
            $table->dropForeign(['alternative_unit_id']);
            $table->dropColumn('alternative_unit_id');
        });
    }
};
