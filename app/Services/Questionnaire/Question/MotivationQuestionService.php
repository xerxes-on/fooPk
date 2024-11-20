<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question related to client motivation.
 *
 * @package App\Services\Questionnaire\Question
 */
final class MotivationQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'motivated',
            'confident',
            'excited',
            'sceptical',
            'insecure'
        ];
    }
}
