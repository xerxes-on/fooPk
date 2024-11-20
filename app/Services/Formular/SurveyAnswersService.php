<?php

namespace App\Services\Formular;

use App\Models\SurveyAnswer;

class SurveyAnswersService
{
    public function getUserAnswer(int $userId, int $questionId): ?string
    {
        $survey = SurveyAnswer::whereUserId($userId)
            ->whereSurveyQuestionId($questionId)
            ->orderBy('id', 'desc')
            ->first();

        return $survey?->answer;
    }
}
