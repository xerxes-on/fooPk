<?php

namespace App\Http\Resources\Questionnaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Api resource representing questionnaire question.
 *
 * @property-read array $resource
 *
 * @package App\Http\Resources
 */
final class QuestionnaireQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Need to pass correct lang for tooltips. Mainly for unauthorized users.
        $lang = $request->user()?->lang ?? $request?->lang;
        $lang = $lang ?? 'de';
        return [
            'id'          => $this->resource['id'],
            'order'       => $this->resource['order'],
            'title'       => $this->resource['title'],
            'subtitle'    => $this->resource['subtitle'],
            'slug'        => $this->resource['slug'],
            'type'        => $this->resource['type'],
            'is_required' => $this->resource['is_required'],
            'progress'    => $this->resource['progress'],
            'options'     => $this->reformatOptions($lang),
            'answer'      => $this->resource['answer'],
        ];
    }

    /**
     * Options should have structure slightly different from what is stored in the DB.
     *
     * Extra tooltips are also added during this phase.
     */
    private function reformatOptions(string $lang): ?array
    {
        if (is_null($this->resource['options'])) {
            return null;
        }

        $updatedOptions = [];

        // TODO: nested options are not supported
        foreach ($this->resource['options'] as $key => $option) {
            $response = [
                'key'   => $key,
                'value' => $option,
            ];

            $tooltip = trans_fb("questionnaire.questions.{$this->resource['slug']}.tooltip.{$key}", '', $lang);
            if ($tooltip !== '') {
                $response['tooltip'] = $tooltip;
            }

            // Add exclusion rules if any
            if (key_exists($key, $this->resource['exclusion_rules'])) {
                $response['exclude'] = $this->resource['exclusion_rules'][$key];
            }

            $updatedOptions[] = $response;
        }

        return $updatedOptions;
    }
}
