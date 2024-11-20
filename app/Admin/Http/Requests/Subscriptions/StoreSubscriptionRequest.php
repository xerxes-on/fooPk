<?php

namespace App\Admin\Http\Requests\Subscriptions;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSubscriptionRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['subscription' => ['required', 'integer']];
    }

    /**
     * Get data to be validated from the request.
     */
    public function validationData(): array
    {
        return array_merge(parent::all(), $this->route()->parameters());
    }
}
