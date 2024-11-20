<?php

declare(strict_types=1);

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\{Ingestion, Recipe};
use App\Rules\UniqueKeyPerArrayRule;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Form request to replace recipe with custom one.
 *
 * @property int $recipe_id Common|Original recipe id
 * @property int|null $custom_recipe_id Custom recipe id
 * @property Carbon $date
 * @property Ingestion $ingestion
 * @property array|null $fixed_ingredients
 * @property array $variable_ingredients
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @property Recipe $recipe
 *
 * @package App\Http\Requests\Recipe
 */
final class CustomFromCommonRecipeCreationRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'        => ['required', 'integer'],
            'custom_recipe_id' => ['nullable', 'integer', 'exists:custom_recipes,id'],
            'date'             => ['required', 'date'],
            'ingestion'        => ['required', 'integer', 'in:' . implode(',', MealTimeEnum::values())],

            'fixed_ingredients'                          => ['nullable', 'array', new UniqueKeyPerArrayRule('replace_by')],
            'fixed_ingredients.*.ingredient_id'          => ['required', 'integer', 'min:1'],
            'fixed_ingredients.*.ingredient_category_id' => ['required', 'integer', 'min:1'],
            'fixed_ingredients.*.amount'                 => ['required', 'numeric', 'min:0'],
            'fixed_ingredients.*.replace_by'             => ['sometimes', 'numeric'],

            'variable_ingredients'                          => ['required', 'array'],
            'variable_ingredients.*.ingredient_id'          => ['required', 'integer', 'min:1'],
            'variable_ingredients.*.ingredient_category_id' => ['required', 'integer', 'min:1'],
            'variable_ingredients.*.replace_by'             => ['sometimes', 'numeric'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        if ((int)$this->custom_recipe_id === 0) {
            $this->offsetUnset('custom_recipe_id');
        }
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $date = Carbon::parse($this->date);
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('date', $e->getMessage());
                    return;
                }
                try {
                    $recipe = Recipe::whereId($this->recipe_id)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $validator->errors()->add('recipe_id', $e->getMessage());
                    return;
                }
                try {
                    $ingestion         = Ingestion::whereId($this->ingestion)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('ingestion', $e->getMessage());
                    return;
                }

                $this->merge([
                    'date'              => $date,
                    'recipe'            => $recipe,
                    'ingestion'         => $ingestion,
                    'ingestionIntValue' => $ingestionIntValue,
                ]);
            }
        ];
    }
}
