<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Enums\Questionnaire\Options\MainGoalQuestionOptionsEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling question about main goal.
 *
 * @package App\Services\Questionnaire\Question
 */
final class MainGoalQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return MainGoalQuestionOptionsEnum::values();
    }

    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $query = QuestionnaireQuestion::query();

        $answer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    $this->questionModel->id
                )
                ->first('answer')?->answer,

            QuestionnaireSourceRequestTypesEnum::WEB => tap(
                app(QuestionnaireUserSession::class)->getAnswersByQuestionIds($this->questionModel->id, (int)$identifier),
                fn(string|array|null $answer) => $query->forWeb()
            ),

            QuestionnaireSourceRequestTypesEnum::WEB_EDITING,
            QuestionnaireSourceRequestTypesEnum::API_EDITING => tap(
                $this->getAnswerForApiEditing($this->questionModel->id, (int)$identifier),
                fn(string|array|null $answer) => $query->baseOnly()
            ),
        };

        if (isset($answer[$this->questionModel->slug]) &&
            in_array($answer[$this->questionModel->slug], MainGoalQuestionOptionsEnum::getWeightDiffOptions(), true)
        ) {
            return $query->nextActive($this->questionModel->order)->firstOrFail();
        }
        // We need to skip weight goal question if user wants to gain/loose weight|TODO: make it more dynamic
        return $query->active()->whereOrder($this->questionModel->order + 2)->firstOrFail();
    }
}
