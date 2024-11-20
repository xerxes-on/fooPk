<?php

namespace Modules\FlexMeal\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request to get Flexmeals with calculated ingredients grouped by ingestions.
 *
 * @property-read string $mealtime
 * @property-read bool|null $separateBreakfast
 * @property-read string|array $page
 *
 * @package App\Http\Requests\Flexmeal
 */
final class FlexMealByIngestionRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mealtime'          => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'separateBreakfast' => ['nullable', 'boolean'],
            'page'              => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $mealtime = in_array($this->mealtime, MealtimeEnum::namesLower()) ? $this->mealtime : MealtimeEnum::BREAKFAST->lowerName();
                if ($mealtime !== MealtimeEnum::BREAKFAST->lowerName() && $this->separateBreakfast) {
                    $mealtime = [MealtimeEnum::LUNCH->lowerName(), MealtimeEnum::DINNER->lowerName()];
                    // resort array to have the selected mealtime first
                    usort($mealtime, function (string $first, string $second) {
                        if ($first === $this->mealtime) {
                            return -1;
                        } elseif ($second === $this->mealtime) {
                            return 1;
                        }
                        return 0;
                    });
                }

                $this->merge(['mealtime' => $mealtime]);
            }
        ];
    }
}
