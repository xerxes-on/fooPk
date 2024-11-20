<?php

namespace App\Admin\Http\Requests\Client;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClientFormRequest
 *
 * @property array $userIds
 * @property array $recipeIds
 *
 * @package App\Http\Requests
 */
final class ClientAddRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'userIds'     => ['required', 'array'],
            'userIds.*'   => ['required', 'integer', 'exists:users,id'],
            'recipeIds'   => ['required', 'array'],
            'recipeIds.*' => ['required', 'integer'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(
            [
                'approve' => $this->approve === 'true',
            ]
        );
    }
}
