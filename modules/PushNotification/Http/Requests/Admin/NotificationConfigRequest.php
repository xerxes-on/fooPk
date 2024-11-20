<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Requests\Admin;

use App\Http\Traits\CanAuthorizeAdminRequests;
use Illuminate\Foundation\Http\FormRequest;
use Modules\PushNotification\Enums\UserGroupOptionEnum;

/**
 * Form request for dispatching notification with config.
 *
 * @property int $id
 * @property array $params
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationConfigRequest extends FormRequest
{
    use CanAuthorizeAdminRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'                                  => ['integer', 'required'],
            'params'                              => ['required', 'array'],
            'params.' . UserGroupOptionEnum::NAME => ['required', 'string'],
            'params.course'                       => ['array', 'nullable'],
            'params.course.id'                    => ['required_with:params.course.status', 'integer', 'nullable'],
            'params.course.status'                => ['required_with:params.course.id', 'integer', 'nullable'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(
            [
                'id'     => (int)$this->id,
                'params' => $this->params,
            ]
        );
    }

    public function messages(): array
    {
        return [
            'params.course.id.required_with'     => trans('PushNotification::validation.params.course.id.required_with'),
            'params.course.status.required_with' => trans('PushNotification::validation.params.course.status.required_with'),
        ];
    }
}
