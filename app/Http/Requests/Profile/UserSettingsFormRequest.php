<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;
use Modules\PushNotification\Enums\NotificationSettingsEnum;

/**
 * Update user settings form request.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $lang
 * @property string|null $old_password
 * @property string|null $new_password
 * @property string|null $notifications
 *
 * @package App\Http\Requests\Profile
 */
final class UserSettingsFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name'    => ['required', 'string'],
            'last_name' => ['string', 'nullable'],
            'lang'          => ['required', 'string'],
            'old_password'  => ['string', 'nullable'],
            'new_password'  => ['string', 'nullable'],
            'notifications' => ['string', 'nullable', 'in:' . implode(',', NotificationSettingsEnum::values())],
        ];
    }
}
