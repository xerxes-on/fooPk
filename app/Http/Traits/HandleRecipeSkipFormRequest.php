<?php

namespace App\Http\Traits;

use App\Enums\MealtimeEnum;
use App\Models\Ingestion;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

trait HandleRecipeSkipFormRequest
{
    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'recipeType' => (int)$this->recipeType
            ]
        );
    }

    /**
     * Add an after validation callback.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $date = Carbon::parse($this->date);
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('date', $e->getMessage());
                    return;
                }
                try {
                    $ingestion         = Ingestion::ofKey($this->mealtime)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('mealtime', $e->getMessage());
                    return;
                }

                $this->merge([
                    'date'              => $date,
                    'ingestion'         => $ingestion,
                    'ingestionIntValue' => $ingestionIntValue
                ]);
            }
        ];
    }
}
