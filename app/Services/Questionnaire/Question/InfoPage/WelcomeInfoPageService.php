<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\InfoPage;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\Question\BaseDependencyQuestionService;
use Illuminate\Database\Eloquent\Model;

/**
 * Service responsible for handling welcome info page.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class WelcomeInfoPageService extends BaseDependencyQuestionService
{
    // Backing the data up...just in case
    protected ?array $dependAnswer = [
        QuestionnaireQuestionSlugsEnum::MAIN_GOAL   => '',
        QuestionnaireQuestionSlugsEnum::WEIGHT_GOAL => '',
        QuestionnaireQuestionSlugsEnum::EXTRA_GOAL  => '',
        QuestionnaireQuestionSlugsEnum::FIRST_NAME  => 'User',
    ];

    public function getVariations(): array
    {
        if (is_null($this->dependAnswer)) {
            return [];
        }
        $header = trans(
            "questionnaire.questions.{$this->questionModel->slug}.options.main_goal",
            [
                'main_goal' => trans(
                    "questionnaire.questions.{$this->questionModel->slug}.replaces.main_goal.{$this->dependAnswer[QuestionnaireQuestionSlugsEnum::MAIN_GOAL]}",
                    locale: $this->locale
                )
            ],
            $this->locale
        );
        $header .= empty($this->dependAnswer[QuestionnaireQuestionSlugsEnum::WEIGHT_GOAL]) ? '.' : trans(
            "questionnaire.questions.{$this->questionModel->slug}.options.weight_goal",
            ['weight_goal' => $this->dependAnswer[QuestionnaireQuestionSlugsEnum::WEIGHT_GOAL]],
            $this->locale
        );

        $footer = '';
        if (!empty($this->dependAnswer['extra_goal'])) {
            $text = array_map(fn($item): string => trans(
                "questionnaire.questions.{$this->questionModel->slug}.replaces.extra_goal.$item",
                locale: $this->locale
            ), $this->dependAnswer[QuestionnaireQuestionSlugsEnum::EXTRA_GOAL]);
            $text = implode(', ', $text);
            // Replace last comma with 'and' separator
            $text = preg_replace("/(, (?!.*, ))/", trans('questionnaire.info_pages.text_separator'), $text);

            $footer .= trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.extra_goal",
                ['extra_goal' => $text],
                $this->locale
            ) . ' ';
        }

        $footer .= trans(
            "questionnaire.questions.{$this->questionModel->slug}.options.end",
            locale: $this->locale
        );

        return ['info' => $header, 'extra' => $footer];
    }

    public function getTitle(): string
    {
        return trans(
            "questionnaire.questions.{$this->questionModel->slug}.title",
            ['name' => trim($this->dependAnswer[QuestionnaireQuestionSlugsEnum::FIRST_NAME])],
            $this->locale
        );
    }

    public function buildDependency(Model $model, int|string $identifier): void
    {
        if ($model::class === QuestionnaireTemporary::class) {
            // TODO: ids hardcoded for now, improve later
            $this->dependAnswer = $model::whereFingerprint($identifier)
                ->whereIn(
                    'questionnaire_question_id',
                    [
                        QuestionnaireQuestionIDEnum::MAIN_GOAL->value,
                        QuestionnaireQuestionIDEnum::WEIGHT_GOAL->value,
                        QuestionnaireQuestionIDEnum::EXTRA_GOAL->value,
                        QuestionnaireQuestionIDEnum::FIRST_NAME->value,
                    ]
                )
                ->pluck('answer')
                ->mapWithKeys(fn(array $item): array => $item)
                ->toArray();
        }
    }
}
