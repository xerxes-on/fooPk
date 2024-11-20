<?php

namespace Modules\Chargebee\Requests\API;

use App\Http\Requests\BaseRequest;
use App\Http\Traits\CanAlwaysAuthorizeRequests;

final class ChargebeeWebHookRequest extends BaseRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['content' => 'required'];
    }
}
