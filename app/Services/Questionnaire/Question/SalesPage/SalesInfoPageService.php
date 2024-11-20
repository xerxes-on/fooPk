<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\SalesPage;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\Question\BaseQuestionService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling Sales info page.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class SalesInfoPageService extends BaseQuestionService
{
    public function getVariations(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }

    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $answer = null;
        if ($model::class === QuestionnaireTemporary::class) {
            // TODO: hardcoded now. maybe need to make it more dynamic
            $answer = $model::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(1)
                ->first()?->answer;
        }
        // TODO: also can be later obtained from Questionnaire model...so need to take into account

        if (isset($answer[QuestionnaireQuestionSlugsEnum::MAIN_GOAL]) &&
            in_array($answer[QuestionnaireQuestionSlugsEnum::MAIN_GOAL], ['lose_weight', 'gain_weight', 'build_muscle'], true)
        ) {
            return QuestionnaireQuestion::nextActive($this->questionModel->order)->firstOrFail();
        }
        // We need to skip weight goal question if user wants to gain/loose weight|TODO: make it more dynamic
        return QuestionnaireQuestion::active()->whereOrder($this->questionModel->order + 2)->firstOrFail();
    }
}
