<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question about client diets.
 *
 * @package App\Services\Questionnaire\Question
 */
final class DietsQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        // TODO:: @NickMost @Andrew check possibility to add into diets slug in DB and configs
        return [
            'ketogenic',
            'low_carb',
            'moderate_carb',
            'paleo',
            'pascetarian',
            'vegetarian',
            'vegan',
            'aip',
            'any'
        ];
    }

    public function getExclusionRules(): array
    {
        return [
            'ketogenic' => [
                'low_carb',
                'moderate_carb',
                'any'
            ],
            'low_carb' => [
                'ketogenic',
                'moderate_carb',
                'any'
            ],
            'moderate_carb' => [
                'ketogenic',
                'low_carb',
                'any'
            ],
            'paleo' => [
                'vegetarian',
                'vegan',
                'aip',
                'any'
            ],
            'pascetarian' => [
                'vegetarian',
                'vegan',
                'aip',
                'any'
            ],
            'vegetarian' => [
                'paleo',
                'pascetarian',
                'vegan',
                'aip',
                'any'
            ],
            'vegan' => [
                'paleo',
                'pascetarian',
                'vegetarian',
                'aip',
                'any'
            ],
            'aip' => [
                'paleo',
                'pascetarian',
                'vegetarian',
                'vegan',
                'any'
            ],
            'any' => [
                'ketogenic',
                'low_carb',
                'moderate_carb',
                'paleo',
                'pascetarian',
                'vegetarian',
                'vegan',
                'aip',
            ]
        ];
    }
}
