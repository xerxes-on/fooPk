<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Requests\API;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Modules\PushNotification\Models\UserNotification;

/**
 * Form request for changing notification status.
 *
 * @property int $id
 * @property UserNotification $notification
 *
 * @used-by \Modules\PushNotification\Http\Controllers\API\PushNotificationApiController::setReadStatus()
 *
 * @package Modules\PushNotification\API
 */
final class NotificationStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['integer', 'required'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        try {
            $notification = $this->user()->pushNotifications()->where('id', $this->id)->firstOrFail();
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages(['id' => "User does not have record #$this->id"]);
        }

        $this->merge(['notification' => $notification]);
    }
}
