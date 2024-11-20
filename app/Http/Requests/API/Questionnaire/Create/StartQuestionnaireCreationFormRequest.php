<?php

namespace App\Http\Requests\API\Questionnaire\Create;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request responsible for starting questionnaire creation.
 *
 * @property string $lang
 * @property string $fingerprint
 *
 * @package App\Http\Requests\Questionnaire
 */
final class StartQuestionnaireCreationFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lang'        => ['string', 'required', 'in:de,en'],
            'fingerprint' => ['string', 'required', 'max:255'],
        ];
    }
}
