<?php

namespace Modules\PushNotification\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Modules\PushNotification\Enums\DeviceTypesEnum;

/**
 * Data necessary to change users password.
 *
 * @property int $user_id
 * @property string $token
 * @property string $type
 * @property string $os_version
 * @property string $app_version
 * @property string $fingerprint
 *
 * @package Modules\PushNotification\API
 */
final class RegisterUserDevice extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token'       => ['string', 'required'],
            'type'        => ['string', 'required', 'in:' . implode(',', DeviceTypesEnum::values())],
            'os_version'  => ['string', 'required', 'max:20'],
            'app_version' => ['string', 'required', 'max:20'],
            'fingerprint' => ['string', 'required', 'max:255'],
        ];
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        return ['user_id' => $this->user()->id, ...parent::validated($key, $default)];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(['user_id' => $this->user()->id]);
    }
}
