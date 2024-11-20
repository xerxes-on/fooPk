<?php

namespace App\Admin\Http\Requests\Client;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for comparing client questionnaire.
 *
 * @property int $clientId
 * @property string $questionnaireId
 *
 * @package App\Http\Requests
 */
final class ClientFormCompareRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'clientId'        => ['required', 'exists:users,id'],
            'questionnaireId' => ['required'],
        ];
    }
}
