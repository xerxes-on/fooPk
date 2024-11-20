<?php

namespace App\Http\Requests\Recipe;

use App\Enums\MealtimeEnum;
use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for obtaining recipes by various filters.
 *
 * @property int $recipe_id
 * @property string|array $mealtime
 * @property array|null $filters
 *
 * @property array|int $page passed as part of pagination
 * @property bool|null $is_app passed but not used
 *
 * @package App\Http\Requests\Recipe
 */
final class GetRecipesByRationRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'recipe_id'           => ['required', 'integer'],
            'mealtime'            => ['required', 'string', 'in:' . implode(',', MealtimeEnum::namesLower())],
            'filters'             => ['array', 'nullable'],
            'filters.complexity'  => ['integer'],
            'filters.cost'        => ['integer'],
            'filters.diet'        => ['integer'],
            'filters.favorite'    => ['integer'],
            'filters.ingestion'   => ['integer'],
            'filters.search_name' => ['string', 'nullable'],
            'filters.seasons'     => ['integer'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'mealtime' => in_array(
                $this->mealtime,
                [MealtimeEnum::LUNCH->lowerName(), MealtimeEnum::DINNER->lowerName()],
                true
            ) ?
                [MealtimeEnum::LUNCH->lowerName(), MealtimeEnum::DINNER->lowerName()] :
                [MealtimeEnum::BREAKFAST->lowerName()],
        ]);
    }
}
