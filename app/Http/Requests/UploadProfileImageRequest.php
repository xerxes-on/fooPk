<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UploadProfileImageRequest
 *
 * @property \Illuminate\Http\File $file
 *
 * @package App\Http\Requests
 */
final class UploadProfileImageRequest extends FormRequest
{
    // TODO: before validation we must do all the first checks (like mime times) and then minimize the image (cropping and resizing)
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['file' => ['image', 'mimes:jpg,jpeg,png', 'max:10000']]; // todo: ahtung! max size is 10mb, is too much!
    }
}
