<?php

namespace App\Http\Requests;

/**
 * Data for update or creation of a post.
 *
 * @property string $content
 * @property \Symfony\Component\HttpFoundation\File\UploadedFile|null|string $image
 * @property string|null $oldImage
 *
 * @package App\Http\Requests
 */
final class PostFormRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->image) && !empty($this->image)) {
            $this->merge(['image' => $this->extractFileName($this->image)]);
        }
        if (is_string($this->oldImage) && !empty($this->oldImage)) {
            $this->merge(['oldImage' => $this->extractFileName($this->oldImage)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // It may be a string (meaning not changes should be made) or file(validate and further update image)
        $imageRule = is_string($this->image) ? ['nullable', 'string'] : ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:10240'];
        return [
            'content'  => ['required', 'string', 'max:65535'],
            'image'    => $imageRule,
            'oldImage' => ['nullable', 'string'] // old image file name
        ];
    }

    private function extractFileName(string $filename): string
    {
        return basename(parse_url($filename)['path']);
    }
}
