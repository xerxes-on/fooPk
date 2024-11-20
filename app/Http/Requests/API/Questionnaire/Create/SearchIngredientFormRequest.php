<?php

namespace App\Http\Requests\API\Questionnaire\Create;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request responsible for searching ingredients.
 *
 * @property string $lang
 * @property string $fingerprint
 * @property string $search
 *
 * @package App\Http\Requests\Questionnaire
 */
final class SearchIngredientFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lang'        => ['required', 'string', 'in:en,de'],
            'fingerprint' => ['required', 'string'],
            'search'      => ['required', 'string'],
        ];
    }
}
