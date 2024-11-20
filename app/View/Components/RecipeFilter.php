<?php

namespace App\View\Components;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Component to render recipe filter item.
 *
 * @package App\View\Components
 */
final class RecipeFilter extends Component
{
    /**
     * Create a new component instance.
     *
     * @param array $filterData Filter data options allowing to build options with values (key => value)
     * @param string $filterTitle Title to render inside label
     * @param string $filterId Input id attribute, also required to be the same as Get request value
     * @param bool $includeDefaultValue Render `All` value option before other options
     * @param string|int $selectedValueDefault default value to set for getting data from $_request
     */
    public function __construct(
        public array      $filterData,
        public string     $filterTitle,
        public string     $filterId,
        public bool       $includeDefaultValue,
        public string|int $selectedValueDefault = 0
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render(): View|Factory
    {
        return view('components.recipe-filter');
    }
}
