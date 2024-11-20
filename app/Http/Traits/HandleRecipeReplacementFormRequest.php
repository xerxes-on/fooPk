<?php

namespace App\Http\Traits;

use App\Enums\MealtimeEnum;
use App\Models\Ingestion;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

trait HandleRecipeReplacementFormRequest
{
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
                    $ingestion         = Ingestion::ofKey($this->ingestion)->firstOrFail();
                    $ingestionIntValue = MealtimeEnum::tryFromValue($ingestion->key)->value;
                } catch (ModelNotFoundException|InvalidArgumentException $e) {
                    $validator->errors()->add('ingestion', $e->getMessage());
                    return;
                }

                $this->merge(
                    [
                        'date'              => $date,
                        'ingestion'         => $ingestion,
                        'ingestionIntValue' => $ingestionIntValue,
                    ]
                );
            }
        ];
    }
}
