<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\InfoPage;

use App\Exceptions\Questionnaire\QuestionDependency;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\Question\BaseDependencyQuestionService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling support info page.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class SupportInfoPageService extends BaseDependencyQuestionService
{
    protected int $dependOnQuestion = 9;

    public function getVariations(): array
    {
        if (!isset($this->dependAnswer['sociability'])) {
            return [];
        }

        return [
            'info' => trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.{$this->dependAnswer['sociability']}",
                locale: $this->locale
            ),
            'extra' => trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.{$this->dependAnswer['sociability']}_support",
                locale: $this->locale
            )
        ];
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
