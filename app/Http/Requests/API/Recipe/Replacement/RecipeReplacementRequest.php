<?php

namespace App\Http\Requests\API\Recipe\Replacement;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Models\CustomRecipe;
use App\Models\Ingestion;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Class RecipeReplacementPayload
 *
 * @property-read Carbon $meal_date
 * @property-read string $ingestion_key
 * @property-read Ingestion $ingestion
 * @property-read int $new_recipe_id
 * @property-read CustomRecipe|Recipe $recipe
 * @property-read int $recipe_type
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests\API\Recipe\Replacement
 */
final class RecipeReplacementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'meal_date'     => ['required', 'date'],
            'ingestion_key' => ['required', 'string'],
            'new_recipe_id' => ['required', 'integer', 'min:1'],
            'recipe_type'   => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $date = Carbon::parse($this->meal_date);
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('meal_date', $e->getMessage());
                    return;
                }

                try {
                    $ingestion         = Ingestion::ofKey($this->ingestion_key)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('ingestion', $e->getMessage());
                    return;
                }

                try {
                    $recipe = (int)$this->recipe_type === RecipeTypeEnum::CUSTOM->value ?
                        CustomRecipe::whereId($this->new_recipe_id)->firstOrFail() :
                        Recipe::whereId($this->new_recipe_id)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $validator->errors()->add('new_recipe_id', $e->getMessage());
                    return;
                }

                $this->merge(
                    [
                        'meal_date'         => $date,
                        'ingestion'         => $ingestion,
                        'recipe'            => $recipe,
                        'ingestionIntValue' => $ingestionIntValue,
                        'recipe_type'       => (int)$this->recipe_type,
                    ]
                );
            }
        ];
    }
}
