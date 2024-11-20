<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionDependencyInterface;
use App\Contracts\Services\Questionnaire\QuestionInterface;
use App\Contracts\Services\Questionnaire\QuestionRequireValidationInterface;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Http\Resources\Questionnaire\QuestionnaireQuestionResource;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Models\User;
use App\Services\Questionnaire\Answer\BaseAnswerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

/**
 * Base question service class.
 * TODO: seems to be getting quite complicated. Try to optimize and simplify
 * @package App\Services\Questionnaire\Question
 */
abstract class BaseQuestionService extends BaseAnswerService implements QuestionInterface
{
    public const OTHER_OPTION_SLUG = 'other';

    /**
     * Model of question.
     */
    protected QuestionnaireQuestion $questionModel;

    /**
     * Answer of question.
     */
    protected null|string|array $questionAnswer = null;

    /**
     * @throws ModelNotFoundException
     */
    public function __construct(
        QuestionnaireTemporary|QuestionnaireQuestion|QuestionnaireAnswer $questionData,
        protected readonly string                                        $locale = 'de',
        string|int|null                                                  $identity = null,
        protected readonly QuestionnaireSourceRequestTypesEnum           $questionnaireType = QuestionnaireSourceRequestTypesEnum::API,
        protected readonly ?User                                         $user = null
    ) {
        if ($questionData instanceof QuestionnaireQuestion) {
            $this->questionModel  = $questionData;
            $this->questionAnswer = $this->tryToGetAnswerByIdentity($identity);
            return;
        }

        if ($questionData instanceof QuestionnaireAnswer) {
            $this->questionModel  = $questionData->question;
            $this->questionAnswer = $questionData->answer;
            return;
        }

        if (empty($questionData->question)) {
            throw new ModelNotFoundException('Question not found');
        }
        $this->questionModel  = $questionData->question;
        $this->questionAnswer = empty($questionData->answer) ? null : $questionData->answer;
    }

    public function mustBeValidated(): bool
    {
        return in_array(QuestionRequireValidationInterface::class, class_implements(static::class));
    }

    public function getTitle(): string
    {
        return trans("questionnaire.questions.{$this->questionModel->slug}.title", locale: $this->locale);
    }

    public function getSubtitle(): ?string
    {
        return trans_fb("questionnaire.questions.{$this->questionModel->slug}.subtitle", null, $this->locale);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $query = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::WEB => QuestionnaireQuestion::forWeb(),
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireQuestion::query(),
            QuestionnaireSourceRequestTypesEnum::API_EDITING,
            QuestionnaireSourceRequestTypesEnum::WEB_EDITING => QuestionnaireQuestion::baseOnly(),
        };

        return $query->nextActive($this->questionModel->order)->firstOrFail();
    }

    public function definePreviousQuestion(Model $model, int|string $identifier): QuestionnaireQuestion
    {
        $query = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::WEB         => QuestionnaireQuestion::forWeb(),
            QuestionnaireSourceRequestTypesEnum::API         => QuestionnaireQuestion::query(),
            QuestionnaireSourceRequestTypesEnum::API_EDITING => QuestionnaireQuestion::baseOnly(),
        };

        return $query->previousActive($this->questionModel->order)->firstOrFail();
    }

    public function hasDependency(): bool
    {
        return in_array(QuestionDependencyInterface::class, class_implements(static::class));
    }

    public function getResource(): QuestionnaireQuestionResource
    {
        return new QuestionnaireQuestionResource($this->prepareResource());
    }

    public function getExclusionRules(): array
    {
        return [];
    }

    protected function prepareResource(): array
    {
        return [
            'id'              => $this->questionModel->id,
            'order'           => $this->questionModel->order,
            'title'           => $this->getTitle(),
            'subtitle'        => $this->getSubtitle(),
            'slug'            => $this->questionModel->slug,
            'type'            => $this->questionModel->type->name,
            'is_required'     => $this->questionModel->is_required,
            'progress'        => $this->getQuestionProgress(),
            'options'         => $this->prepareOptions(),
            'exclusion_rules' => $this->getExclusionRules(),
            'answer'          => $this->getAnswer(),
        ];
    }

    protected function prepareOptions(): array
    {
        return in_array(
            $this->questionModel->type,
            [QuestionnaireQuestionTypesEnum::INFO_PAGE, QuestionnaireQuestionTypesEnum::SALES_PAGE],
            true
        ) ?
            $this->getVariations() :
            Arr::mapWithKeys(
                $this->getVariations(),
                fn(string $item): array => [
                    $item => trans(
                        "questionnaire.questions.{$this->questionModel->slug}.options.$item",
                        locale: $this->locale
                    )
                ]
            );
    }

    protected function getQuestionProgress(): float
    {
        $questions = match ($this->questionnaireType) {
            QuestionnaireSourceRequestTypesEnum::WEB => QuestionnaireQuestion::active()->forWeb()->pluck('order'),
            QuestionnaireSourceRequestTypesEnum::API => QuestionnaireQuestion::active()->pluck('order'),
            QuestionnaireSourceRequestTypesEnum::API_EDITING,
            QuestionnaireSourceRequestTypesEnum::WEB_EDITING => QuestionnaireQuestion::active()->baseOnly()->pluck('order'),
        };
        $questionIndex = $questions->search(fn($question): bool => $question === $this->questionModel->order);
        return round(((int)$questionIndex + 1) * 100 / $questions->count());
    }
}
