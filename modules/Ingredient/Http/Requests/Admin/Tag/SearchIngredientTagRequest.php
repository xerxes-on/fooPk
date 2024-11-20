<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Requests\Admin\Tag;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for searching ingredient tags.
 *
 * @property string|null $search_name
 *
 * @package Modules\Ingredient\Http\Requests\Admin\Tag
 */
final class SearchIngredientTagRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

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
