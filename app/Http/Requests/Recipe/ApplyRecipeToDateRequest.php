<?php

declare(strict_types=1);

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\Ingestion;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Form request for recipe replacement.
 *
 * @property int $recipe_id    ID of initial recipe
 * @property string $mealtime
 * @property string|\Carbon\Carbon $date
 * @property string|int $recipe_type
 *
 * @property-read Recipe $recipe    ID of initial recipe
 * @property-read Ingestion $ingestion
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests
 */
final class ApplyRecipeToDateRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'   => ['required', 'integer'],
            'mealtime'    => ['required', 'integer', 'in:' . implode(',', MealtimeEnum::values())],
            'date'        => ['required', 'date'],
            'recipe_type' => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
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
                    $date = Carbon::parse($this->date);
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('date', $e->getMessage());
                    return;
                }
                try {
                    $recipe = Recipe::where('id', $this->recipe_id)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $validator->errors()->add('recipe_id', $e->getMessage());
                    return;
                }
                try {
                    $ingestion         = Ingestion::whereId($this->mealtime)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('mealtime', $e->getMessage());
                    return;
                }

                $this->merge([
                    'date'              => $date,
                    'recipe'            => $recipe,
                    'ingestion'         => $ingestion,
                    'ingestionIntValue' => $ingestionIntValue,
                    'recipe_type'       => (int)$this->recipe_type,
                ]);
            }
        ];
    }
}
