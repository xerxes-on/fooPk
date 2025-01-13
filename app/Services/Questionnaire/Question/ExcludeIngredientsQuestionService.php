<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Exceptions\Questionnaire\QuestionValidation;
use DB;
use Modules\Ingredient\Models\Ingredient;

/**
 * Service responsible for handling question related to client excluded ingredients.
 *
 * @package App\Services\Questionnaire\Question
 */
final class ExcludeIngredientsQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            'exclude_ingredients'
        ];
    }

    public function validateOverApi(string $answer): bool
    {
        try {
            $parsedAnswer = json_decode($answer, true);
            $this->validateAnswerStructureForSlug($parsedAnswer);

            // TODO: should ids be validated? How it should be validated in the end?
            //            if (array_unique(array_diff($parsedAnswer[$this->questionModel->slug], array_keys($this->getVariations()))) !== []) {
            //                throw new QuestionValidation(trans('questionnaire.validation.answer.value', locale: $this->locale));
            //            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        return true;
    }

    public function reformatAnswerFromWeb(null|string|array $answer): string|array
    {
        if (is_null($answer)) {
            return [];
        }

        if (empty($answer)) {
            return $answer;
        }

        $answer = array_map('intval', $answer);
        sort($answer, SORT_NUMERIC);
        return $answer;
    }

    public function getFormattedAnswer(): string
    {
        if (empty($this->questionAnswer)) {
            return '';
        }
        $answer = DB::table('ingredient_translations')
            ->where('locale', $this->locale)
            ->whereIn('ingredient_id', $this->questionAnswer)
            ->pluck('name')
            ->toArray();
        return implode(', ', $answer);
    }

    public function getAnswer(): string|array|null
    {
        // Pull the base answer from questionAnswer:
        $baseAnswer = $this->questionAnswer[$this->questionModel->slug] ?? $this->questionAnswer;

        $userExcludedIngredients = $this->user
            ? $this->user->excludedIngredients->pluck('id')->toArray()
            : [];

        // Ensure both are arrays, remove duplicates, and sort numerically
        $baseAnswer = $baseAnswer ?? [];
        $baseAnswer = array_unique(
            array_merge((array) $baseAnswer, $userExcludedIngredients),
            SORT_NUMERIC
        );
        sort($baseAnswer, SORT_NUMERIC);

        if (empty($baseAnswer)) {
            return null;
        }

        // Load ingredient data (with translations) for the final "display" answer.
        $data = Ingredient::withOnly('translations')
            ->whereIn('id', $baseAnswer)
            ->get();

        // Build array of { key => ingredient id, value => localized name }:
        $formattedAnswer = [];
        foreach ($data as $item) {
            $formattedAnswer[] = [
                'key'   => $item->id,
                'value' => $item->translations
                        ->where('locale', $this->locale)
                        ->first()
                        ?->name ?? $item->name
            ];
        }

        return $formattedAnswer;
    }

}
