<?php

namespace App\Admin\Http\Requests;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Str;

/**
 * Validate diet store form request
 *
 * @property int|null $id
 * @property string $slug
 * @property string $name
 *
 * @package App\Http\Requests
 */
final class DietFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo(PermissionEnum::CREATE_INGREDIENT_DIETS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'   => ['integer', 'nullable'],
            'slug' => ['required', 'string', 'max:30', 'unique:App\Models\Diet,slug'],
            'name' => ['required', 'string']
        ];
    }

    /**
     * Add an after validation callback.
     * @param null $key
     * @param null $default
     */
    public function validated($key = null, $default = null): array
    {
        $validated         = parent::validated($key, $default);
        $validated['name'] = sanitize_string($validated['name']);
        $validated['slug'] = Str::slug(sanitize_string($validated['slug']), '_');
        return $validated;
    }

}
