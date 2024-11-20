<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Requests\API;

use App\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;
use Modules\FlexMeal\Services\FlexmealUpdateValidationService;

/**
 * Update Request for a FlexMeal over API.
 *
 * @property-read string $meal
 * @property-read string|null $flexmeal
 * @property-read string|null $notes
 * @property-read \Illuminate\Http\UploadedFile|null $new_image
 * @property-read array $ingredients
 * @property-read null|string $signature
 * @property-read \App\Models\User $user
 * @property-read \Modules\FlexMeal\Models\FlexmealLists $listModel
 * @property-read bool $regenerateMealPlan
 *
 * @package Modules\FlexMeal\Http\Requests\API
 */
final class FlexMealUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'meal'                        => ['required', 'string', 'max:20'],
            'flexmeal'                    => ['string', 'nullable', 'max:191'], // Title
            'notes'                       => ['string', 'nullable', 'max:65530'],
            'new_image'                   => ['image', 'nullable', 'max:10240', 'mimes:jpg,jpeg,png'],
            'ingredients'                 => ['required', 'array', 'min:1'],
            'ingredients.*.amount'        => ['required', 'numeric', 'min:0', 'max:65535'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'min:1'],
            'signature'                   => ['nullable', 'string']
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'ingredients.required' => trans('common.please_add_ingredient'),
            'ingredients.min'      => trans('common.please_add_ingredient')
        ];
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user       = $this->user();
                $flexmealId = $this->route('list_id');

                try {
                    $listModel = $user?->flexmealLists()->setEagerLoads([])->findOrFail($flexmealId);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('record', trans('common.nothing_found'));
                    return;
                }

                $validationService = app(
                    FlexmealUpdateValidationService::class,
                    [
                        'flexmealId' => $flexmealId,
                        'newMeal'    => $this->meal,
                        'oldMeal'    => $listModel->mealtime,
                        'user'       => $user,
                        'signature'  => $this->signature
                    ]
                );
                $validationService->performConfirmationCheck();

                $name  = sanitize_string($this->flexmeal);
                $notes = sanitize_string($this->notes);
                $this->merge(
                    [
                        'user'               => $user,
                        'flexmeal'           => empty($name) ? now()->format('d.m.Y') : $name,
                        'notes'              => empty($this->notes) ? null : $notes,
                        'listModel'          => $listModel,
                        'regenerateMealPlan' => $validationService->isMealPlanReplacementRequired()
                    ]
                );
            }
        ];
    }
}
