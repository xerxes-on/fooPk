<?php

declare(strict_types=1);

namespace Modules\Ingredient\Services;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Models\User;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;

/**
 * Service for syncing user excluded ingredients with questionnaire answers.
 *
 * @package Modules\Ingredient\Services
 */
final class UserExcludedIngredientsSyncService
{
    public function syncWithQuestionnaire(User $user, array $latestQuestionnaireAnswers): void
    {
        $user->excludedIngredients()->sync($this->extractIngredients($latestQuestionnaireAnswers));
    }

    public function store(User $user, ?array $ingredients = []): void
    {
        $user->excludedIngredients()->detach();

        if (!empty($ingredients)) {
            $user->excludedIngredients()->attach($ingredients);
        }
    }

    private function extractIngredients(array $answers): array
    {
        $ingredients = [];
        foreach ($answers as $answer) {
            if (isset($answer['questionnaire_question_id']) && $answer['questionnaire_question_id'] === QuestionnaireQuestionIDEnum::EXCLUDE_INGREDIENTS->value) {
                $ingredients = (array)$answer['answer'];
                break;
            }
        }
        return $ingredients;
    }
}
