<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\SalesPage;

use App\Exceptions\NoMoreQuestions;
use App\Models\QuestionnaireQuestion;
use App\Services\Questionnaire\Question\BaseQuestionService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling info page related to app benefits.
 *
 * @note This is a dummy service, as this page would be developed and devoted only to mobile applications.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class BenefitsInfoPageService extends BaseQuestionService
{
    public function getVariations(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }

    /**
     * @throws \App\Exceptions\NoMoreQuestions
     */
    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        throw new NoMoreQuestions('No more questions');
    }
}
