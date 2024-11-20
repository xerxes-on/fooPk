<?php

namespace App\Admin\Http\Requests\Recipe\Tag;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for searching recipe tags.
 *
 * @property string|null $search_name
 *
 * @package App\Http\Requests\Admin\Recipe\Tag
 */
final class SearchRecipeTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search_name' => ['nullable', 'string']
        ];
    }
}
