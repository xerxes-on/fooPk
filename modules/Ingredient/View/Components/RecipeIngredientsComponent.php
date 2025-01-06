<?php

declare(strict_types=1);

namespace Modules\Ingredient\View\Components;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Ingredient\Enums\IngredientCategoryEnum;
use Modules\Ingredient\Services\IngredientConversionService;

class RecipeIngredientsComponent extends Component
{
    public bool $isNotSpice;
    public bool $isConvertable;

    public function __construct(public array $ingredient, public Recipe $recipe, public int $recipeType, public bool $isForMealPlan)
    {
        $this->isNotSpice    = $ingredient['main_category'] != IngredientCategoryEnum::SEASON->value;
        $this->isConvertable = $ingredient[IngredientConversionService::KEY] !== [];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('ingredient::components.recipe-ingredients');
    }
}
