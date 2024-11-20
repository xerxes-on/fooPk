<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a questionnaire answer.
 *
 * @property array $question probably array bu should be checked
 * @property array $answer probably array bu should be checked
 */
final class SurveyAnswer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $question           = (new SurveyQuestion($this->question))->toArray($request);
        $question['answer'] = $this->formatAnswer($this->answer);
        return $question;
    }

    /**
     * Format an answer the way FE expects it.
     *
     * @param string|array|null $answer
     *
     * @return array
     */
    public static function formatAnswer(string|array|null $answer): array
    {
        $is_string = is_string($answer);

        if (is_null($answer)) {
            return [];
        } elseif ($is_string && $answer != '' && $answer[0] == '{') {
            $answerData = json_decode($answer, true);
            $response   = [];
            array_walk(
                $answerData,
                function ($value, $key) use (&$response) {
                    // Answers related to training contains only number and should be passes as is.
                    $response[$key] = in_array($key, ['count', 'time'], true) ? $value :
                        trans_fb("survey_questions.$key", $value);
                }
            );
            return $response;
        } elseif ($is_string) {
            return ['value' => $answer];
        }

        return $answer;
    }
}
