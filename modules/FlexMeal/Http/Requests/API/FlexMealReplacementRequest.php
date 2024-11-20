<?php

namespace Modules\FlexMeal\Http\Requests\API;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\Ingestion;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Data for assigning a flexmeal recipe to a meal.
 *
 * @property int $recipe ID of initial recipe
 * @property-read int $flexmeal_id ID of flexmeal to replace with
 * @property Carbon $date
 * @property string $mealtime
 * @property int $recipeType
 * @property \App\Models\Ingestion $ingestion
 *
 * @property-read \Modules\FlexMeal\Models\FlexmealLists $flexmeal
 * @property-read int $ingestionIntValue Value of ingestion obtained from enum
 *
 * @package App\Http\Requests\API
 */
final class FlexMealReplacementRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe'      => ['required', 'integer'],
            'flexmeal_id' => ['required', 'integer', 'min:1'],
            'date'        => ['required', 'date'],
            'mealtime'    => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'recipeType'  => ['required', 'integer', 'in:' . implode(',', RecipeTypeEnum::values())],
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
                    $ingestion         = Ingestion::ofKey($this->mealtime)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('mealtime', $e->getMessage());
                    return;
                }
                try {
                    $flexmeal = $this->user()->flexmealLists()->whereId($this->flexmeal_id)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('flexmeal_id', "You don't owe such flexmeal");
                    return;
                }

                $this->merge(
                    [
                        'date'              => $date,
                        'ingestion'         => $ingestion,
                        'flexmeal'          => $flexmeal,
                        'ingestionIntValue' => $ingestionIntValue,
                    ]
                );
            }
        ];
    }
}
