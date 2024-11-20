<?php

namespace Modules\FlexMeal\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Http\Requests\BaseRequest;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;

/**
 * Form request to delete a flexmeal.
 *
 * @property-read integer $flexmeal_id
 * @property-read string $desired_mealtime
 * @property-read \Modules\FlexMeal\Models\FlexmealLists $flexmeal
 *
 * @package App\Http\Requests\Flexmeal
 */
final class CheckFlexmealRequest extends BaseRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'flexmeal_id'      => ['required', 'integer', 'min:1'],
            'desired_mealtime' => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
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
                    $flexmeal = $this->user()?->flexmealLists()->findOrFail($this->flexmeal_id);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('flexmeal_id', 'Flexmeal not found');
                    return;
                }

                $this->merge(['flexmeal' => $flexmeal]);
            }
        ];
    }
}
