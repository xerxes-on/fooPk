<?php

declare(strict_types=1);

namespace Modules\Ingredient\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class IngredientSwitchToggleComponent extends Component
{
    public array $helpText;

    public function __construct()
    {
        $this->helpText = [
            'title'   => trans('ingredient::common.switcher_tip.title'),
            'content' => trans('ingredient::common.switcher_tip.description')
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('ingredient::components.ingredient-switch-toggle');
    }
}
