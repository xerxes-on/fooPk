<?php

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\Ingestion;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request for custom recipe replacement.
 *
 * @property int $recipe
 * @property string $mealTime
 * @property string|\Carbon\Carbon $date
 * @property string $recipeType
 * @property array $ingredients
 * @property Ingestion $ingestion
 *
 * @package App\Http\Requests
 * @deprecated
 */
final class CustomRecipeReplacement extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe'                      => ['required', 'integer', 'min:1'],
            'mealTime'                    => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'date'                        => ['required', 'date'],
            'recipeType'                  => ['required', 'string', 'in:recipe,custom_recipe,flexmeal'],
            'ingredients'                 => ['required', 'array', 'size:3'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'min:1']
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
                } catch (InvalidFormatException) {
                    $validator->errors()->add('date', 'Unable to parse date field');
                    return;
                }

                try {
                    $ingestion = Ingestion::ofKey($this->mealTime)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('mealTime', 'Unknown meal type');
                    return;
                }

                $this->merge(
                    [
                        'date'      => $date,
                        'ingestion' => $ingestion,
                    ]
                );
            }
        ];
    }
}
