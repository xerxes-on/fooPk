<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    private array $tables = [
        'allergy_to_diets',
        'allergy_to_ingredients',
        'ingredient_categories_to_diets',
        'ingredient_seasons',
        'ingredients_to_ingredient_attributes',
        'ingredients_to_custom_recipes',
        'recipe_ingredients',
        'recipes_to_categories',
        'recipes_to_diets',
        'recipes_to_ingestions',
        'recipes_to_inventories',
        'recipes_to_users',
        'user_bulk_exclusions',
        'user_excluded_ingredients',
        'user_excluded_recipes',
        'user_recipe',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $item) {
            if (Schema::hasColumn($item, 'id')) {
                Schema::table($item, function (Blueprint $table) {
                    $table->dropColumn('id');
                });
            }
            Schema::table($item, function (Blueprint $table) {
                $table->id()->first();
            });
        }

        Schema::table('recipe_variable_ingredients', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable(false)->useCurrent()->change();
            $table->timestamp('updated_at')
                ->nullable(false)
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->change();
        });

        if (Schema::hasColumn('seasons', 'name')) {
            Schema::table('seasons', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->char('lang', 2)->default('de')->change();
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->index(['user_id', 'recipe_id']);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('recipes_to_categories');
        Schema::dropIfExists('recipe_category');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $item) {
            if (Schema::hasColumn($item, 'id')) {
                Schema::table($item, function (Blueprint $table) {
                    $table->dropColumn('id');
                });
            }
        }
        Schema::table('seasons', function (Blueprint $table) {
            $table->string('name');
        });
        Schema::table('recipe_variable_ingredients', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->default(null)->change();
            $table->timestamp('updated_at')->nullable()->default(null)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('notes');
            $table->string('lang')->default('de')->change();
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex(['recipe_id', 'user_id']);
        });

        Schema::create('recipe_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('recipes_to_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('recipe_id');
            $table->unique(['category_id', 'recipe_id']);
            $table->unique(['recipe_id', 'category_id']);
            $table->index('category_id');
            $table->index('recipe_id');
            $table->foreign('category_id')->references('id')->on('recipe_category')->onDelete('cascade');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });
    }
};
