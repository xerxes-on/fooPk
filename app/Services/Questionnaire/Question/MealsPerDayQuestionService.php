<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionDependencyInterface;
use App\Enums\Questionnaire\Options\MealPerDayQuestionOptionsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling question about meals per day preferences.
 *
 * @package App\Services\Questionnaire\Question
 */
final class MealsPerDayQuestionService extends BaseValidationRequireQuestionService implements QuestionDependencyInterface
{
    protected null|string|array $dependAnswer = null;

    public function getVariations(): array
    {
        $answer = array_key_exists(QuestionnaireQuestionSlugsEnum::DIETS, (array)$this->dependAnswer) ?
            $this->dependAnswer[QuestionnaireQuestionSlugsEnum::DIETS] :
            $this->dependAnswer;
        // Due to lack of recipes we need to limit meals per day options depending on selected diet
        if (in_array('moderate_carb', (array)$answer)) {
            return [
                MealPerDayQuestionOptionsEnum::STANDARD->value,
                MealPerDayQuestionOptionsEnum::LUNCH_DINNER->value
            ];
        }
        return MealPerDayQuestionOptionsEnum::values();
    }

    public function getDependentQuestionId(): int
    {
        return QuestionnaireQuestionIDEnum::DIETS->value;
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionDependency
     */
    public function buildDependency(Model $model, int|string $identifier): void
    {
        // todo: need to test it properly as user can be empty
        $this->dependAnswer = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::API,
            QuestionnaireSourceRequestTypesEnum::API_EDITING => QuestionnaireTemporary::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId(
                    $this->getDependentQuestionId()
                )
                ->first('answer')?->answer,
            QuestionnaireSourceRequestTypesEnum::WEB => app(QuestionnaireUserSession::class)
                ->getAnswersByQuestionIds($this->getDependentQuestionId(), auth()->id()),
            QuestionnaireSourceRequestTypesEnum::WEB_EDITING => $this
                ?->user
                ?->latestQuestionnaireSpecificAnswer($this->getDependentQuestionId())
                ?->first()
                ?->answers
                ?->pluck('answer')
                ?->flatten()
                ->toArray()
        };

        if (is_null($this->dependAnswer)) {
            throw new QuestionDependency(
                "Unable to build dependency for question with id: `{$this->questionModel->id}` because of dependency on question with id: `{$this->getDependentQuestionId()}` which is not answered yet or just missing."
            );
        }
    }

    /**
     * Altered answer for question in case there is an answers in DB that should be excluded.
     */
    public function getAnswer(): string|array|null
    {
        $answer = $this->questionAnswer[$this->questionModel->slug] ?? ($this->questionAnswer ?? null);

        if (is_null($answer)) {
            return null;
        }

        if (is_null($this->dependAnswer)) {
            try {
                $this->buildDependency($this->questionModel, $this->questionModel->id);
            } catch (QuestionDependency $e) {
                $this->dependAnswer = null;
            }
        }

        $dependAnswer = array_key_exists(QuestionnaireQuestionSlugsEnum::DIETS, (array)$this->dependAnswer) ?
            $this->dependAnswer[QuestionnaireQuestionSlugsEnum::DIETS] :
            $this->dependAnswer;
        // In response for answer we need to prevent excluded options if they were saved previously
        if (
            in_array('moderate_carb', (array)$dependAnswer) &&
            in_array($answer, [
                MealPerDayQuestionOptionsEnum::BREAKFAST_LUNCH->value,
                MealPerDayQuestionOptionsEnum::BREAKFAST_DINNER->value
            ], true)
        ) {
            return null;
        }

        return $answer;
    }
}
