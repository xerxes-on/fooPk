<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question related to client difficulties.
 *
 * @package App\Services\Questionnaire\Question
 */
final class DifficultiesQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'change_habits',
            'deal_cravings',
            'deal_emotional_eating',
            'deal_overeating',
            'time_absence',
            'none'
        ];
    }

    public function getExclusionRules(): array
    {
        return [
            'change_habits' => [
                'none'
            ],
            'deal_cravings' => [
                'none'
            ],
            'deal_emotional_eating' => [
                'none'
            ],
            'deal_overeating' => [
                'none'
            ],
            'time_absence' => [
                'none'
            ],
            'none' => [
                'change_habits',
                'deal_cravings',
                'deal_emotional_eating',
                'deal_overeating',
                'time_absence',
            ],
        ];
    }
}
