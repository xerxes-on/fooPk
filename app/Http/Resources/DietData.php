<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of user diet data.
 *
 * @package App\Http\Resources
 */
final class DietData extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'KH' => [
                'label'    => trans('common.carbohydrates'),
                'value'    => $this->resource['KH'],
                'percents' => $this->resource['kh_percents'],
            ],
            'EW' => [
                'label'    => trans('common.protein'),
                'value'    => $this->resource['EW'],
                'percents' => $this->resource['ew_percents'],
            ],
            'F' => [
                'label'    => trans('common.fat'),
                'value'    => $this->resource['F'],
                'percents' => $this->resource['f_percents'],
            ],
            'Kcal' => [
                'label' => trans('common.calories'),
                'value' => $this->resource['Kcal'],
            ],
            'additional'        => $this->resource['additional'] ?? null,
            'notices'           => $this->resource['notices'],
            'predefined_values' => $this->preparePredefinedValues($this->resource['predefined_values']),
            'ingestion'         => $this->addIngestionLabels()
        ];
    }

    /**
     * Convert predefined values to its corresponding numeric values.
     */
    private function preparePredefinedValues(array $values): array
    {
        $output = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $output[$key]['breakfast']['percents'] = convertToNumber(data_get($value, 'breakfast.percents'));
                $output[$key]['lunch']['percents']     = convertToNumber(data_get($value, 'lunch.percents'));
                $output[$key]['dinner']['percents']    = convertToNumber(data_get($value, 'dinner.percents'));
                continue;
            }
            $output[$key] = convertToNumber($value);
        }
        return $output;
    }

    /**
     * Add translations label to each ingestion section.
     */
    private function addIngestionLabels(): array
    {
        $ingestions = $this->resource['ingestion'];
        foreach ($ingestions as $key => $value) {
            $ingestions[$key]['percents'] = convertToNumber($value['percents']);
            $ingestions[$key]['Kcal']     = convertToNumber($value['Kcal']);
            $ingestions[$key]['KH']       = convertToNumber($value['KH']);
            $ingestions[$key]['EW']       = convertToNumber($value['EW']);
            $ingestions[$key]['F']        = convertToNumber($value['F']);
            $ingestions[$key]['label']    = trans('common.' . $key);
        }
        return $ingestions;
    }
}
