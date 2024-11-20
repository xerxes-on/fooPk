<?php

namespace App\Admin\Http\Requests\Client;

use App\Helpers\Calculation;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClientFormRequest
 *
 * @property array $userIds
 * @property int $amount
 * @property array|null $seasons
 * @property string $distribution_type
 * @property int $breakfast_snack
 * @property int $lunch_dinner
 * @property int $recipes_tag
 * @property string $distribution_mode
 *
 * @package App\Http\Requests
 */
final class ClientAddRandomRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'userIds'           => ['required', 'array'],
            'userIds.*'         => ['required', 'integer', 'exists:users,id'],
            'amount'            => ['required', 'integer'],
            'seasons'           => ['nullable', 'array',],
            'seasons.*'         => ['integer'],
            'distribution_type' => ['required', 'string', 'in:ingestions,general'],
            'breakfast_snack'   => ['integer'],
            'lunch_dinner'      => ['integer'],
            'recipes_tag'       => ['required', 'integer'],
            'distribution_mode' => [
                'required',
                'string',
                'in:' . Calculation::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_STRICT . ',' . Calculation::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_PREFERABLE
            ],
        ];
    }
}
