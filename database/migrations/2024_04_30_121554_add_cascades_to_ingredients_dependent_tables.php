<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('ingredients_to_custom_recipes', static function (Blueprint $table) {
            $table->unsignedInteger('ingredient_id')->change();
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->cascadeOnDelete();
        });
        Schema::table('ingredients_to_ingredient_attributes', static function (Blueprint $table) {
            $table->unsignedInteger('ingredient_id')->change();
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->cascadeOnDelete();

            $table->unsignedInteger('ingredient_attribute_id')->change();
            $table->foreign('ingredient_attribute_id', 'ingr_to_ingr_attr_ingr_attr_id_frgn')->references('id')->on('ingredient_attributes')->cascadeOnDelete();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('ingredients_to_custom_recipes', static function (Blueprint $table) {
            $table->dropForeign(['ingredient_id']);
        });
        Schema::table('ingredients_to_ingredient_attributes', static function (Blueprint $table) {
            $table->dropForeign(['ingredient_id']);
            $table->dropForeign('ingr_to_ingr_attr_ingr_attr_id_frgn');
        });
    }
};
