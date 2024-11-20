<?php

namespace App\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FormularFormRequest
 * @deprecated
 * @package App\Http\Requests
 */
class FormularFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            //'text'    => 'required',
        ];
    }
}
