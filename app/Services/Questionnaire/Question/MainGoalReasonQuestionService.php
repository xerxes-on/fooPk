<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionDependencyInterface;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling question about user mail goal reason.
 *
 * @package App\Services\Questionnaire\Question
 */
final class MainGoalReasonQuestionService extends BaseValidationRequireQuestionService implements QuestionDependencyInterface
{
    protected int $dependOnQuestion = 1;

    protected ?array $dependAnswer = null;

    public function getVariations(): array
    {
        //TODO:: refactor with enums
        return match ($this->dependAnswer['main_goal'] ?? '') {
            'lose_weight' => [
                'improve_health',
                'boost_confidence',
                'event_preparation',
            ],
            'gain_weight' => [
                'improve_health',
                'boost_confidence',
                'event_preparation',
                'improve_fitness'
            ],
            'build_muscle' => [
                'improve_health',
                'boost_confidence',
                'event_preparation',
                'improve_metabolism',
                'prevent_age_muscle_loss',
            ],
            default => [
                'improve_health',
                'boost_confidence',
                'event_preparation',
                'improve_fitness',
                'improve_metabolism',
                'prevent_age_muscle_loss',
            ]
        };
    }

    public function getTitle(): string
    {
        return trans(
            "questionnaire.questions.{$this->questionModel->slug}.title",
            [
                'reason' => strtolower(
                    trans(
                        "questionnaire.questions.main_goal.options.{$this->dependAnswer['main_goal']}",
                        locale: $this->locale
                    )
                )
            ],
            $this->locale
        );
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionDependency
     */
    public function buildDependency(Model $model, int|string $identifier): void
    {
        // todo: need to test it properly as user can be empty
        $this->dependAnswer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    $this->dependOnQuestion
                )
                ->first('answer')?->answer,
            QuestionnaireSourceRequestTypesEnum::WEB => app(QuestionnaireUserSession::class)
                ->getAnswersByQuestionIds($this->dependOnQuestion, auth()->id()),
        };

        if (is_null($this->dependAnswer)) {
            throw new QuestionDependency(
                "Unable to build dependency for question with id: `$this->questionModel->id` because of dependency on question with id: `$this->dependOnQuestion` which is not answered yet or just missing."
            );
        }
    }

    public function getDependentQuestionId(): int
    {
        return $this->dependOnQuestion;
    }
}
