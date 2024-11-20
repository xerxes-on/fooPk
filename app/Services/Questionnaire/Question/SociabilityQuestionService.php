<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question about client sociability.
 *
 * @package App\Services\Questionnaire\Question
 */
final class SociabilityQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'alone',
            'community'
        ];
    }
}
