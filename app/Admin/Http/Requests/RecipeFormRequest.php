<?php

declare(strict_types=1);

namespace App\Admin\Http\Requests;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Recipe\RecipeStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * Form request responsible for validating saving recipe data.
 *
 * @property int|null $id
 * @property string $title
 * @property array|null $inventory
 * @property array|null $ingestions
 * @property array|null $ingredients
 * @property array $variable_ingredients
 * @property int $complexity_id
 * @property int $price_id
 * @property array $total
 * @property int|float|null $min_kcal
 * @property int|float|null $max_kcal
 * @property int|float|null $min_kh
 * @property int|float|null $max_kh
 * @property int|float|null $cooking_time
 * @property string|null $unit_of_time
 * @property string|null $image
 * @property string|null $oldImage
 * @property int|null $status
 * @property bool|null $translations_done
 * @property array|null $related
 * @property array $steps
 * @property array $ingredientIds Generic data of collection of ingredients
 *
 * @package App\Http\Requests
 */
final class RecipeFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_RECIPE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // TODO: must determine which fields are required
        return [
            'id'                                   => ['integer', 'nullable'],
            'title'                                => ['required', 'string', 'max:191'], // Restricted due to DB column size
            'inventory'                            => ['array', 'nullable'],
            'inventory.*'                          => ['integer'],
            'ingestions'                           => ['array', 'nullable'],
            'ingestions.*'                         => ['integer'],
            'ingredients'                          => ['array', 'nullable'],
            'ingredients.*.amount'                 => ['numeric'],
            'ingredients.*.ingredient_id'          => ['numeric'],
            'variable_ingredients'                 => ['array', 'required'],
            'variable_ingredients.*.ingredient_id' => ['numeric'],
            'variable_ingredients.*.category_id'   => ['numeric'],
            'complexity_id'                        => ['nullable', 'integer', 'min:1', 'max:3'],
            'price_id'                             => ['nullable', 'integer', 'min:1', 'max:3'],
            'total'                                => ['required', 'array', 'size:4'],
            'total.proteins'                       => ['required', 'numeric'],
            'total.fats'                           => ['required', 'numeric'],
            'total.carbohydrates'                  => ['required', 'numeric'],
            'total.calories'                       => ['required', 'numeric'],
            'min_kcal'                             => ['numeric', 'nullable'],
            'max_kcal'                             => ['numeric', 'nullable'],
            'min_kh'                               => ['numeric', 'nullable'],
            'max_kh'                               => ['numeric', 'nullable'],
            'cooking_time'                         => ['integer', 'nullable'],
            'unit_of_time'                         => ['string', 'nullable', 'in:minutes,hours'],
            'image'                                => ['string', 'nullable'],
            'oldImage'                             => ['string', 'nullable'],
            'status'                               => ['integer', 'nullable'],
            'translations_done'                    => ['boolean', 'nullable'],
            'related'                              => ['array', 'nullable'],
            'steps'                                => ['array', 'nullable'],
            'steps.*.description'                  => ['string', 'max:65535'],
            'steps.*.id'                           => ['integer'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'steps.*.description.string' => ['string' => 'Step :position cannot be empty'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(
            [
                'ingredientIds' => $this->generateIngredientIdArray(),
                'steps'         => $this->steps ?? [],
            ]
        );
    }

    /**
     * Get the validated data from the request.
     *
     * @param array|int|string|null $key
     * @param mixed $default
     */
    public function validated($key = null, $default = null): array
    {
        $validated = [
            'creator'           => $this->user()->id,
            'complexity_id'     => $this->complexity_id,
            'price_id'          => $this->price_id,
            'proteins'          => $this->total['proteins'],
            'fats'              => $this->total['fats'],
            'carbohydrates'     => $this->total['carbohydrates'],
            'calories'          => $this->total['calories'],
            'min_kcal'          => $this->min_kcal,
            'max_kcal'          => $this->max_kcal,
            'min_kh'            => $this->min_kh,
            'max_kh'            => $this->max_kh,
            'cooking_time'      => $this->cooking_time,
            'unit_of_time'      => $this->unit_of_time,
            'image'             => $this->image,
            'status'            => $this->status ?? RecipeStatusEnum::DRAFT->value, // all new recipes must be draft
            'translations_done' => (bool)$this->translations_done,
            'title'             => $this->title
        ];

        if ((!empty($this->image) && !empty($this->oldImage)) && ($this->image === $this->oldImage)) {
            unset($validated['image']);
        }

        return $validated;
    }

    /**
     * Calculate recipe diets from recipe ingredients.
     */
    private function generateIngredientIdArray(): array
    {
        return Arr::whereNotNull(
            Arr::flatten(
                Arr::map(
                    [$this->ingredients ?? [], $this->variable_ingredients],
                    static function (array $ingredients) {
                        $result = [];
                        foreach ($ingredients as $ingredient) {
                            if (isset($ingredient['ingredient_id']) && $ingredient['ingredient_id'] != '0') {
                                $result[] = $ingredient['ingredient_id'];
                            }
                        }
                        return $result;
                    }
                )
            )
        );
    }
}
