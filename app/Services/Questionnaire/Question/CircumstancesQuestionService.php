<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question about users current circumstances.
 *
 * @package App\Services\Questionnaire\Question
 */
final class CircumstancesQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'fast_food',
            'regular_cooking',
            'cooking_additionally',
            'supportive_environment',
            'unsupportive_environment',
            'neutral_environment',
        ];
    }

    public function getExclusionRules(): array
    {
        return [
            'fast_food'              => ['regular_cooking'],
            'regular_cooking'        => ['fast_food'],
            'supportive_environment' => [
                'unsupportive_environment',
                'neutral_environment',
            ],
            'unsupportive_environment' => [
                'supportive_environment',
                'neutral_environment',
            ],
            'neutral_environment' => [
                'supportive_environment',
                'unsupportive_environment',
            ],
        ];
    }
}
