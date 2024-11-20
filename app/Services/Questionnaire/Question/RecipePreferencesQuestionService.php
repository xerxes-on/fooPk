<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question related to client recipe preferences.
 *
 * @package App\Services\Questionnaire\Question
 */
final class RecipePreferencesQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        //TODO:: refactor with enums
        return [
            'quick_meals',
            'meal_prep',
            'cost_effective',
            'family_friendly',
            'any'
        ];
    }
}
