<?php

namespace Modules\FlexMeal\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to delete a flexmeal.
 *
 * @property-read integer $list_id
 *
 * @package App\Http\Requests\Flexmeal
 */
final class DeleteFlexmealRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'list_id' => ['required', 'integer', 'min:1']
        ];
    }
}
