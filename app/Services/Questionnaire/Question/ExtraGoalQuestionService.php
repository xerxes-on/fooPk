<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionDependencyInterface;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 *  Service responsible for handling question about clients extra goals.
 *
 * @package App\Services\Questionnaire\Question
 */
final class ExtraGoalQuestionService extends BaseValidationRequireQuestionService implements QuestionDependencyInterface
{
    protected int $dependOnQuestion = 1;

    protected null|string|array $dependAnswer = null;

    public function getVariations(): array
    {
        return [
            'improve_daily_energy',
            'reduce_body_fat',
            'build_muscle',
            'become_defined',
            'improve_skin',
            'improve_intestine',
            'improve_immune',
            'improve_sleep',
            'improve_food_relationship',
        ];
    }

    protected function prepareOptions(): array
    {
        $options = $this->getVariations();
        $key     = false;
        switch ($this->dependAnswer['main_goal'] ?? '') {
            case 'lose_weight':
                $key = array_search('reduce_body_fat', $options);
                break;
            case 'improve_fitness':
                $key = array_search('improve_daily_energy', $options);
                break;
            case 'build_muscle':
                $key = array_search('build_muscle', $options);
                break;
        }
        if ($key !== false) {
            unset($options[$key]);
        }

        return Arr::mapWithKeys(
            $options,
            fn(string $item) => [
                $item => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.options.$item",
                    locale: $this->locale
                )
            ]
        );
    }

    public function definePreviousQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $query = QuestionnaireQuestion::query();

        $answer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    $this->dependOnQuestion
                )
                ->first('answer')?->answer,

            QuestionnaireSourceRequestTypesEnum::WEB => tap(
                app(QuestionnaireUserSession::class)->getAnswersByQuestionIds($this->dependOnQuestion, (int)$identifier),
                fn(string|array|null $answer) => $query->forWeb()
            ),

            QuestionnaireSourceRequestTypesEnum::API_EDITING => tap(
                $this->getAnswerForApiEditing($this->dependOnQuestion, (int)$identifier),
                fn(string|array|null $answer) => $query->baseOnly()
            )
        };

        // Can occasionally be string
        $answer = is_array($answer) ? $answer['main_goal'] : $answer;

        if (in_array($answer, ['lose_weight', 'gain_weight'], true)) {
            return $query->previousActive($this->questionModel->order)->firstOrFail();
        }

        return $query->active()->whereOrder($this->questionModel->order - 2)->firstOrFail();
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionDependency
     */
    public function buildDependency(Model $model, int|string $identifier): void
    {
        // TODO: need more tests as user is not always can be obtained
        $this->dependAnswer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    $this->dependOnQuestion
                )
                ->first('answer')?->answer,
            // todo: auth can break some parts. testing is required, mainly for admin side
            QuestionnaireSourceRequestTypesEnum::WEB => app(QuestionnaireUserSession::class)
                ->getAnswersByQuestionIds($this->dependOnQuestion, ($this->user->id ?? auth()->id())),
            QuestionnaireSourceRequestTypesEnum::API_EDITING => $this->getAnswerForApiEditing(
                $this->dependOnQuestion,
                (int)$identifier
            ),
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
