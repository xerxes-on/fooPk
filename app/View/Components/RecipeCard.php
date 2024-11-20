<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Recipe;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Component to render recipe card.
 *
 * @package App\View\Components
 */
final class RecipeCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @param \App\Models\Recipe $recipe Recipe model instance
     * @param int $key Iteration key (used in generation of vue components)
     * @param bool $lockItem Mark item as locked
     * @param bool $showIngredients Show ingredients preview
     */
    public function __construct(
        public Recipe $recipe,
        public int    $key,
        public bool   $lockItem,
        public bool   $showIngredients
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render(): View|Factory
    {
        return view(
            'components.recipe',
            [
                'recipeItemClass' => $this->gatherMainClassAttributes(),
                'diets'           => $this->buildDietsString()
            ]
        );
    }

    /**
     * Generate main root element class names.
     *
     * @return string[]
     */
    private function gatherMainClassAttributes(): array
    {
        $recipeItemClass = ['search-recipes_list_item'];

        if ($this->recipe?->calc_invalid) {
            $recipeItemClass[] = 'invalid_recipe';
        }

        if (!is_null($this->recipe?->excluded)) {
            $recipeItemClass[] = 'excluded_recipe';
        }

        return $recipeItemClass;
    }

    private function buildDietsString(): string
    {
        if ($this->recipe->diets->count() === 0) {
            return '';
        }

        return '<span class="recipe-diets-item">' .
            implode(
                '</span><span class="recipe-diets-item">',
                $this->recipe->diets->pluck('name')->toArray()
            )
            . '</span>';
    }
}
