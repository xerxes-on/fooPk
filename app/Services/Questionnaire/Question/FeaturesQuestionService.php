<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\NoMoreQuestions;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling question related to clients desired features.
 *
 * @package App\Services\Questionnaire\Question
 */
final class FeaturesQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'shopping_list',
            'community',
            'support',
            'weekly_plan',
            'recipes_to_needs',
            'challenges',
            'knowledge_content',
            'seasonal_recipes',
        ];
    }

    /**
     * @throws NoMoreQuestions
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        return match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::WEB => throw new NoMoreQuestions('No more questions'),
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireQuestion::nextActive($this->questionModel->order)
                ->firstOrFail(),
        };
    }
}
