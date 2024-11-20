<?php

namespace App\Admin\Http\Requests\Recipe;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use App\Models\{Recipe, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * Form request for deleting user recipe in meal plan by admin.
 *
 * @property int $userId
 * @property array $recipes
 * @property User $user
 * @property Collection $recipeCollection
 *
 * @package App\Http\Requests\Admin\Recipe
 */
final class DeleteBulkRecipeRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'userId'    => ['required', 'integer', 'min:1'],
            'recipes'   => ['required', 'array', 'min:1'],
            'recipes.*' => ['integer'],
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $user = User::whereId($this->userId)->firstOrFail();
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('userId', "User with id #$this->userId is not found");
                    return;
                }

                $recipeCollection = Recipe::whereIn('id', $this->recipes)->setEagerLoads([])->get(['id']);
                if ($recipeCollection->isEmpty()) {
                    $validator->errors()->add('recipes', 'No matches found for selected recipes');
                    return;
                }

                $this->merge(
                    [
                        'user'             => $user,
                        'recipeCollection' => $recipeCollection,
                    ]
                );
            }
        ];
    }
}
