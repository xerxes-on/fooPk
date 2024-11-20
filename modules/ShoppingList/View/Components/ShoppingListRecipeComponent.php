<?php

declare(strict_types=1);

namespace Modules\ShoppingList\View\Components;

use App\Enums\{MealtimeEnum, Recipe\RecipeTypeEnum};
use App\Models\{CustomRecipe, Recipe};
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use Modules\FlexMeal\Models\FlexmealLists;

final class ShoppingListRecipeComponent extends Component
{
    public function __construct(public CustomRecipe|FlexmealLists|Recipe $recipe)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if ($this->recipe instanceof CustomRecipe) {
            $recipeType     = RecipeTypeEnum::CUSTOM->value;
            $originalRecipe = $this->recipe->originalRecipe;
            $title          = sprintf(
                '%s *%s*',
                $this->recipe->title,
                trans('common.edited')
            );
            $routeUrl = route('recipe.show.custom.common', [
                'id'        => $this->recipe->id,
                'date'      => $this->recipe->pivot->meal_day,
                'ingestion' => MealtimeEnum::tryToGetLowerNameFromValue($this->recipe->pivot->mealtime)
            ]);
            $mealtime = $originalRecipe?->ingestion ?? $this->recipe->ingestion; // null if edited
        } elseif ($this->recipe instanceof FlexmealLists) {
            $recipeType     = RecipeTypeEnum::FLEXMEAL->value;
            $originalRecipe = $this->recipe;
            $title          = $originalRecipe->name;
            $routeUrl       = route(
                'recipes.flexmeal.show_one',
                [
                    'id'        => $this->recipe->id,
                    'date'      => $this->recipe->pivot->meal_day,
                    'ingestion' => MealtimeEnum::tryToGetLowerNameFromValue($this->recipe->pivot->mealtime)
                ]
            );
            $mealtime = $originalRecipe?->ingestion;
        } elseif ($this->recipe instanceof Recipe) {
            $recipeType     = RecipeTypeEnum::ORIGINAL->value;
            $originalRecipe = $this->recipe;
            $title          = $originalRecipe->title;
            $routeUrl       = route('recipe.show', [
                'id'        => $this->recipe->id,
                'date'      => $this->recipe->pivot->meal_day,
                'ingestion' => MealtimeEnum::tryToGetLowerNameFromValue($this->recipe->pivot->mealtime)
            ]);
            $mealtime = $originalRecipe?->ingestions;
        }

        $mealtime = $mealtime instanceof Collection ?
            implode(' / ', $mealtime->pluck('title')->toArray()) :
            $mealtime->title;

        return view('shopping-list::components.shopping-list-recipe', [
            'recipeType'     => $recipeType,
            'originalRecipe' => $originalRecipe,
            'title'          => $title,
            'routeUrl'       => $routeUrl,
            'mealtime'       => $mealtime
        ]);
    }
}
