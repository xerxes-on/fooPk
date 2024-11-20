<?php

namespace App\Http\Traits\Questionnaire;

use Log;

trait CanReformatNumericValues
{
    public function reformatAnswerFromApi(string $answer): string
    {
        $parsedAnswer = json_decode($answer, true);
        if (empty($parsedAnswer[$this->questionModel->slug])) {
            return $answer;
        }
        $processedValue                           = str_replace([',', '.'], '.', $parsedAnswer[$this->questionModel->slug]);
        $parsedAnswer[$this->questionModel->slug] = $processedValue;
        return json_encode($parsedAnswer);
    }

    public function reformatAnswerFromWeb(null|string|array $answer): string
    {
        if (is_null($answer)) {
            return '';
        }
        if (is_array($answer)) {
            Log::warning(
                'Question answer is array, but it should be string. Question slug: ' . $this->questionModel->slug,
                [
                    'answer'   => $answer,
                    'question' => $this->questionModel->toArray(),
                    'user'     => auth()->user()?->id,
                    'request'  => request()->all()
                ]
            );
            return '';
        }
        return str_replace([',', '.'], '.', $answer);
    }
}
