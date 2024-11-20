<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Requests\Admin;

use App\Http\Traits\CanAuthorizeAdminRequests;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form request for creating or editing notification.
 *
 * @property int|null $id
 * @property int $type_id
 * @property string|null $link
 * @property array|null $en
 * @property array|null $de
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationRequest extends FormRequest
{
    use CanAuthorizeAdminRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return
            [
                'id'      => ['integer', 'nullable'],
                'type_id' => ['integer', 'required'],
                'link'    => ['url', 'nullable', 'max:190'],
                ...RuleFactory::make(
                    [
                        '%title%'      => ['required', 'string'],
                        '%content%'    => ['required_with:translations.%title%', 'string'],
                        '%link_title%' => ['string', 'nullable', 'max:190'],
                    ]
                )
            ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // notification does not contain link
                if ($this->containsOnlyNull([$this->de['link_title'], $this->en['link_title'], $this->link])) {
                    return;
                }

                // One translation is missing
                if (in_array(null, [$this->de['link_title'], $this->en['link_title']])) {
                    $missingTranslationKeys = array_keys(
                        array_filter(
                            ['de' => $this->de['link_title'], 'en' => $this->en['link_title']],
                            static fn($val) => is_null($val)
                        )
                    );
                    array_walk($missingTranslationKeys, static function (string $val) use ($validator) {
                        $validator->errors()->add("$val.link_title", trans('validation.attributes.notification.translations'));
                    });
                }

                // notification contains link but missing translation de
                if (!is_null($this->link) && is_null($this->de['link_title'])) {
                    $validator->errors()->add('de.link_title', trans('validation.attributes.notification.translations'));
                }

                // notification contains link but missing translation en
                if (!is_null($this->link) && is_null($this->en['link_title'])) {
                    $validator->errors()->add('en.link_title', trans('validation.attributes.notification.translations'));
                }

                // We have translations but link missing
                if (!in_array(null, [$this->de['link_title'], $this->en['link_title']]) && is_null($this->link)) {
                    $validator->errors()->add('link', trans('validation.attributes.notification.link'));
                }
            }
        ];
    }

    /**
     * Check if array contains only null values.
     */
    private function containsOnlyNull(array $input): bool
    {
        return empty(array_filter($input, static fn($val) => $val !== null));
    }
}
