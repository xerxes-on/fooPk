<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\InfoPage;

use App\Exceptions\Questionnaire\QuestionDependency;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\Question\BaseDependencyQuestionService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling info page related to client motivation.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class MotivationInfoPageService extends BaseDependencyQuestionService
{
    protected int $dependOnQuestion = 23;

    public function getTitle(): string
    {
        if (is_null($this->dependAnswer)) {
            return '';
        }

        $key = 'title';

        if (in_array($this->dependAnswer['motivation'], ['sceptical', 'insecure'], true)) {
            $key = 'title_alternative';
        }

        return trans("questionnaire.questions.{$this->questionModel->slug}.$key", locale: $this->locale);
    }

    public function getVariations(): array
    {
        if (is_null($this->dependAnswer)) {
            return [];
        }

        return in_array($this->dependAnswer['motivation'], ['sceptical', 'insecure'], true) ?
            [
                'info' => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.options.info_negative",
                    locale: $this->locale
                ),
                'extra' => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.options.extra_negative",
                    locale: $this->locale
                ),
            ] :
            [
                'info' => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.options.info_positive",
                    locale: $this->locale
                ),
                'extra' => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.options.extra_positive",
                    locale: $this->locale
                ),
            ];
    }

    protected function prepareOptions(): array
    {
        return $this->getVariations();
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionDependency
     */
    public function buildDependency(Model $model, int|string $identifier): void
    {
        if ($model::class === QuestionnaireTemporary::class) {
            $this->dependAnswer = $model::whereFingerprint($identifier)
                ->whereQuestionnaireQuestionId($this->dependOnQuestion)
                ->first()?->answer;
        }
        // TODO: also can be later obtained from Questionnaire model...so need to take into account
        if (is_null($this->dependAnswer)) {
            throw new QuestionDependency(
                "Unable to build dependency for question with id: `$this->questionModel->id` because of dependency on question with id: `$this->dependOnQuestion` which is not answered yet or just missing."
            );
        }
    }
}
