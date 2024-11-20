<?php

namespace Modules\PushNotification\Http\Requests\Admin;

use App\Http\Traits\CanAuthorizeAdminRequests;
use File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Form request for creating or editing notification type.
 *
 * @property int|null $id
 * @property string|null $icon
 * @property string|null $oldImage
 * @property string $name
 * @property string $slug
 * @property boolean|null $is_important
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationTypeRequest extends FormRequest
{
    use CanAuthorizeAdminRequests;

    private int $allowedIconSize   = 102400; // 100KB
    private array $allowedIconMime = ['image/jpg', 'image/png', 'image/jpeg'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @note We won't be able to validate image size and type here, because it is not uploaded with form.
     * Upload is done via ajax. We need to make any validation manually.
     */
    public function rules(): array
    {
        return [
            'id'           => ['integer', 'nullable'],
            'icon'         => ['string', 'nullable'], // Validated manually
            'oldImage'     => ['string', 'nullable'],
            'name'         => ['required', 'string', 'min:3', 'max:60'],
            'slug'         => ['required', 'string', 'min:3', 'max:20'],
            'is_important' => ['boolean', 'nullable'],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param array|int|string|null $key
     * @param mixed $default
     */
    public function validated($key = null, $default = null): array
    {
        $validated = [
            'icon'         => $this?->icon ?? STAPLER_NULL,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'is_important' => (bool)$this->is_important,
        ];

        if ((!empty($this->icon) && !empty($this->oldImage)) && ($this->icon === $this->oldImage)) {
            unset($validated['icon']);
        }

        return $validated;
    }

    /**
     * Handle a passed validation attempt.
     *
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        $this->validateIcon();
        $this->merge(
            [
                'name' => sanitize_string($this->name),
                'slug' => Str::slug(sanitize_string($this->slug), '_'),
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    private function validateIcon(): void
    {
        if (is_null($this->icon)) {
            return;
        }
        $path = public_path($this->icon);
        // Existence check
        if (!File::exists($path)) {
            throw ValidationException::withMessages(
                [
                    'icon' => trans('validation.attributes.notification.icon.missing'),
                ]
            );
        }
        // Size check
        if (File::size($path) > $this->allowedIconSize) {
            throw ValidationException::withMessages(
                [
                    'icon' => trans(
                        'validation.attributes.notification.icon.size',
                        [
                            'size' => '<b>100KB</b>',
                            'link' => 'https://tinypng.com/'
                        ]
                    ),
                ]
            );
        }
        // Type check
        if (!in_array(File::mimeType($path), $this->allowedIconMime)) {
            throw ValidationException::withMessages(
                [
                    'icon' => trans(
                        'validation.attributes.notification.icon.type',
                        ['type' => custom_implode(['jpg', 'png', 'jpeg'], '</b>, <b>', '<b>', '</b>')]
                    ),
                ]
            );
        }
    }
}
