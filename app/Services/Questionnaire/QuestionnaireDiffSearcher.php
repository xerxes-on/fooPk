<?php

namespace App\Services\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Exceptions\Questionnaire\QuestionnaireMissing;
use App\Models\QuestionnaireAnswer;
use App\Models\User;
use Illuminate\Support\Arr;

/**
 * Service to save new users temporarily questionnaire into constant one.
 *
 * @note Initially it was designed to find certain diffs in answers, but it was later simplified just to find any.
 *
 * @package App\Services\Questionnaire
 */
final class QuestionnaireDiffSearcher
{
    private array|null $newAnswers = [];
    private array|null $oldAnswers = [];

    /**
     * find diffs in answers between latest questionnaire and new one.
     * Method return true in case there is any difference between answers.
     * Be aware: answers must have format like:
     * <pre>
     * [
     *    [
     *        id => int,
     *        slug => string,
     *        answer => string|array
     *    ]
     * ]
     * </pre>
     */
    public function findDiffWithLatestQuestionnaireOverWeb(User $user, array $answers): bool
    {
        try {
            return $this->prepareData($user, $answers)->findAnyAnswersDiff();
        } catch (QuestionnaireMissing) {
            return false;
        }
    }

    /**
     * Prepare data to be processed further.
     * @throws QuestionnaireMissing
     */
    private function prepareData(User $user, array $answers): QuestionnaireDiffSearcher
    {
        $latestQuestionnaire = $user->latestBaseQuestionnaire()->first();
        if ($latestQuestionnaire === null) {
            throw new QuestionnaireMissing();
        }
        $this->oldAnswers = Arr::flatten($latestQuestionnaire
            ->answers
            ->mapWithKeys(static fn(QuestionnaireAnswer $item): array => [
                $item->question->slug => [
                    'id'      => $item->question->id,
                    'slug'    => $item->question->slug,
                    'answers' => $item->answer
                ]
            ])
            ->sortBy('id')
            ->toArray());
        usort($answers, static fn(array $firstAnswer, array $secondAnswer): int => $firstAnswer['id'] - $secondAnswer['id']);
        $this->newAnswers = Arr::flatten(Arr::mapWithKeys($answers, static function ($item): array {
            $answers = $item['answers'];
            if (in_array($item['slug'], [QuestionnaireQuestionSlugsEnum::ALLERGIES, QuestionnaireQuestionSlugsEnum::DISEASES], true)) {
                // As before saving old answers we filter out empty values, we should do the same here
                $answers = array_filter($answers, static fn($item) => !empty($item));
            }
            return [
                $item['slug'] => [
                    'id'      => $item['id'],
                    'slug'    => $item['slug'],
                    'answers' => $answers
                ]
            ];
        }));
        return $this;
    }

    /**
     * Get diff between two new and old answers.
     * If true returned - answers are the different.
     */
    private function findAnyAnswersDiff(): bool
    {
        return
            !empty(array_diff_assoc((array)$this->oldAnswers, (array)$this->newAnswers))
            ||
            !empty(array_diff_assoc((array)$this->newAnswers, (array)$this->oldAnswers));
    }
}
