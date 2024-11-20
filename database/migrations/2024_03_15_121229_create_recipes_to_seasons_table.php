<?php

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipes_to_seasons', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('recipe_id');
            $table->unsignedInteger('seasons_id');

            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
            $table->foreign('seasons_id')->references('id')->on('seasons')->cascadeOnDelete();

            $table->timestamp('created_at')->nullable(false)->useCurrent();
            $table->timestamp('updated_at')->nullable(false)->useCurrent()->useCurrentOnUpdate();
        });

        Recipe::chunk(200, static function (Collection $recipes) {
            foreach ($recipes as $recipe) {
                /** @var Recipe $recipe */
                $recipe->seasons()->attach($recipe->getSeasonIds()->pluck('id')->toArray());
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(static function () {
            Schema::dropIfExists('recipes_to_seasons');
        });
    }
};
