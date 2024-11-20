<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Custom translations.
 *
 * @property-read string $language
 * @property-read \Illuminate\Http\UploadedFile $translations
 *
 * @package App\Http\Requests\API
 */
final class TranslationsPayload extends FormRequest
{
    /**
     * Only certain users should be able to upload translations.
     */
    public function authorize(): bool
    {
        // hardcoded, it should be enough for now
        return $this->user()?->email === 'itechuser@foodpunk.de';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'language'     => ['required', 'string', 'size:2'],
            'translations' => ['required', 'file'],
        ];
    }
}
