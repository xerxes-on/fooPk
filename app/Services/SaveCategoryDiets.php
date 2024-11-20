<?php

namespace App\Services;

use Modules\Ingredient\Models\IngredientCategory;

/**
 * Service to save category diets.
 * TODO: refactor this
 * @package App\Services
 */
final class SaveCategoryDiets
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(private array $diets, private ?IngredientCategory $model = null)
    {
        if ($this->model === null) {
            $this->model = new IngredientCategory();
        }
    }

    /**
     * Execute the service.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->model->diets()->detach();

        $dietsToSave = [];
        foreach ($this->diets as $diet) {
            $dietsToSave[] = [
                'diet_id' => $diet
            ];
        }

        $this->model->diets()->attach($dietsToSave);
    }
}
