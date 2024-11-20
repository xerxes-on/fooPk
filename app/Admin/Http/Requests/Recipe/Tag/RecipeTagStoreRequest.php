<?php

namespace App\Admin\Http\Requests\Recipe\Tag;

use App\Enums\Admin\Permission\PermissionEnum;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Form request for creating or editing notification type.
 *
 * @property int|null $id
 * @property string $slug
 * @property bool|null $filter
 * @property bool|null $is_randomize
 * @property array $recipes
 * @property string $name
 *
 * @used-by \App\Admin\Http\Controllers\Recipe\RecipeTagAdminController::store()
 *
 * @package App\Http\Requests\Admin\Recipe\Tag
 */
final class RecipeTagStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_RECIPE_TAGS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'          => ['integer', 'nullable'],
            'slug'        => ['required', 'string', 'min:3', 'max:20'],
            'filter'      => ['bool', 'nullable'],
            'is_internal' => ['bool', 'nullable'],
            'recipes'     => ['required', 'array', 'min:1'],
            ...RuleFactory::make(
                [
                    '%title%' => ['required', 'string'],
                ]
            )
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
        $validated = parent::validated($key, $default);

        if (isset($validated['recipes'])) {
            unset($validated['recipes']);
        }

        $validated['filter'] ??= false;
        $validated['is_internal'] ??= false;

        $validated['slug'] = Str::slug(sanitize_string($validated['slug']), '_');

        return $validated;
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(
            [
                'slug' => Str::slug(sanitize_string($this->slug), '_'),
            ]
        );
    }
}
