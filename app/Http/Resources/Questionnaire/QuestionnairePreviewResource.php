<?php

namespace App\Http\Resources\Questionnaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Api resource representing questionnaire data.
 *
 * @property-read array $resource
 *
 * @package App\Http\Resources
 */
final class QuestionnairePreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $result = [];
        foreach ($this->resource['questions'] as $question) {
            $result[] = [
                'title'  => trans("questionnaire.questions.$question->slug.title"),
                'answer' => $this->resource['answers'][$question->slug] ?? '',
            ];
        }

        return $result;
    }
}
