<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionRequireValidationInterface;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Exceptions\Questionnaire\QuestionValidation;

abstract class BaseValidationRequireQuestionService extends BaseQuestionService implements QuestionRequireValidationInterface
{
    protected string $validationMessage = '';

    public function getValidationErrorMessage(): string
    {
        return $this->validationMessage;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        // Check structure for answer range in case multiple choices available
        if (is_array($answer)) {
            if (array_unique(array_diff(array_keys($answer), $this->getVariations())) !== []) {
                $this->validationMessage = trans('questionnaire.validation.answer.value', locale: $this->locale);
                return false;
            }
            return true;
        }

        // Check structure for answer range
        if (!in_array($answer, $this->getVariations(), true)) {
            $this->validationMessage = trans('questionnaire.validation.answer.value', locale: $this->locale);
            return false;
        }

        return true;
    }

    public function validateOverApi(string $answer): bool
    {
        $parsedAnswer = json_decode($answer, true);

        try {
            if ($this->questionModel->type == QuestionnaireQuestionTypesEnum::RADIO) {
                $this->validateSimpleStructure($parsedAnswer);
            } else {
                $this->validateComplexStructure($parsedAnswer);
            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionValidation
     */
    protected function validateSimpleStructure(?array $parsedAnswer): void
    {
        $this->validateAnswerStructureForSlug($parsedAnswer);

        // Skip validation only if question is not mandatory and is missing
        if (empty($parsedAnswer[$this->questionModel->slug]) && !$this->questionModel->is_required) {
            return;
        }

        // Check structure for answer range
        if (!in_array($parsedAnswer[$this->questionModel->slug], $this->getVariations(), true)) {
            throw new QuestionValidation(trans('questionnaire.validation.answer.value', locale: $this->locale));
        }
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionValidation
     */
    protected function validateComplexStructure(?array $parsedAnswer): void
    {
        $this->validateAnswerStructureForSlug($parsedAnswer);

        // Skip validation only if question is not mandatory and is missing
        if (empty($parsedAnswer[$this->questionModel->slug]) && !$this->questionModel->is_required) {
            return;
        }

        // Answer should not be empty if question is mandatory
        if (empty($parsedAnswer[$this->questionModel->slug]) && $this->questionModel->is_required) {
            throw new QuestionValidation(trans('questionnaire.validation.answer.empty', locale: $this->locale));
        }

        // Allow only recognized variations
        if (array_unique(array_diff($parsedAnswer[$this->questionModel->slug], $this->getVariations())) !== []) {
            throw new QuestionValidation(trans('questionnaire.validation.answer.value', locale: $this->locale));
        }
    }

    /**
     * @throws \App\Exceptions\Questionnaire\QuestionValidation
     */
    protected function validateAnswerStructureForSlug(?array $parsedAnswer): void
    {
        // Check structure for slug
        if (is_null($parsedAnswer) || !array_key_exists($this->questionModel->slug, $parsedAnswer)) {
            throw new QuestionValidation(trans('questionnaire.validation.answer.structure', locale: $this->locale));
        }
    }
}
