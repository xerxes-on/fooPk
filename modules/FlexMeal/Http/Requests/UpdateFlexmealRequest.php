<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Requests;

use App\Enums\MealtimeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\FlexMeal\Services\FlexmealUpdateValidationService;

/**
 * Form request to update a flexmeal over WEB.
 *
 * @property-read int|string $id
 * @property-read string $flexmeal Title of flexmeal
 * @property-read string $name Title of flexmeal
 * @property-read string $meal
 * @property-read array $ingredients
 * @property-read string|null $notes
 * @property-read \Illuminate\Http\UploadedFile|null $image
 * @property-read string $old_image
 * @property-read null|string $signature
 * @property-read \App\Models\User $user
 * @property-read \Modules\FlexMeal\Models\FlexmealLists $listModel
 * @property-read bool $regenerateMealPlan
 *
 * @package Modules\FlexMeal\Http\Requests
 */
final class UpdateFlexmealRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'                          => ['required', 'integer'],
            'flexmeal'                    => ['string', 'nullable', 'max:191'], // Title
            'meal'                        => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'ingredients'                 => ['required', 'array', 'min:1'],
            'ingredients.*.amount'        => ['required', 'numeric', 'min:0', 'max:65535'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'min:1'],
            'notes'                       => ['string', 'nullable', 'max:65530'],
            'image'                       => ['image', 'nullable', 'max:10240', 'mimes:jpg,jpeg,png'],
            'old_image'                   => ['string', 'nullable'],
            'signature'                   => ['nullable', 'string']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
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
                $user = $this->user();
                try {
                    $listModel = $user?->flexmealLists()->setEagerLoads([])->findOrFail($this->id);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('id', trans('validation.exists', ['attribute' => 'id']));
                    return;
                }

                $validationService = app(
                    FlexmealUpdateValidationService::class,
                    [
                        'flexmealId' => $this->id,
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
                        'name'               => empty($name) ? now()->format('d.m.Y') : $name,
                        'notes'              => empty($this->notes) ? null : $notes,
                        'image'              => $this->file('image'),
                        'listModel'          => $listModel,
                        'regenerateMealPlan' => $validationService->isMealPlanReplacementRequired()
                    ]
                );
            }
        ];
    }
}
