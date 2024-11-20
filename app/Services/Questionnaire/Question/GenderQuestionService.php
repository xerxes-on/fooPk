<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question related to clients gender.
 *
 * @package App\Services\Questionnaire\Question
 */
final class GenderQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'male',
            'female',
        ];
    }
}
