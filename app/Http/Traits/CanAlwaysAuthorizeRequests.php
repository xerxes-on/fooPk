<?php

namespace App\Http\Traits;

trait CanAlwaysAuthorizeRequests
{
    /**
     * Determine if the user is authorized to make this request.
     */
    final public function authorize(): bool
    {
        return true;
    }
}
