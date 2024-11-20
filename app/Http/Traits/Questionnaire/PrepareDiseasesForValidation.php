<?php

namespace App\Http\Traits\Questionnaire;

use App\Exceptions\Questionnaire\QuestionValidation;
use App\Models\Allergy;

trait PrepareDiseasesForValidation
{
    protected function prepareKeysForValidation(array $answer): array
    {
        $receivedKeys = [];
        foreach ($answer as $value) {
            if (is_array($value)) {
                $receivedKeys[] = array_keys($value)[0];
                continue;
            }
            $receivedKeys[] = $value;
        }
        return $receivedKeys;
    }

    protected function hasWrongAnswerStructure(array $parsedAnswer): bool
    {
        return array_unique(
            array_diff(
                $this->prepareKeysForValidation($parsedAnswer),
                array_keys($this->getVariations())
            )
        ) !== [];
    }

    public function getFormattedAnswer(): string
    {
        $allergies = Allergy::whereIn('slug', $this->questionAnswer)->get()->pluck("name:$this->locale", 'slug')->toArray();
        $return    = '';
        foreach ($this->questionAnswer as $key => $answer) {
            if ($key === self::OTHER_OPTION_SLUG) {
                $return .= trans('common.other') . " : $answer, ";
                continue;
            }
            if (isset($allergies[$answer])) {
                $return .= $allergies[$answer] . ', ';
            }
        }
        return trim($return, ', ');
    }

    public function getAnswer(): string|array|null
    {
        $answer = parent::getAnswer();
        if (is_null($answer)) {
            return null;
        }
        if (is_array($answer)) {
            $answer = array_filter($answer, fn($value, $key) => !empty($value), ARRAY_FILTER_USE_BOTH);
        }
        if (empty($answer)) {
            return null;
        }
        return $answer;
    }

    protected function prepareOptions(): array
    {
        return $this->getVariations();
    }

    public function validateOverApi(string $answer): bool
    {
        try {
            $parsedAnswer = json_decode($answer, true);
            $this->validateAnswerStructureForSlug($parsedAnswer);

            // Here we require ONLY keys from variations!
            if ($this->hasWrongAnswerStructure($parsedAnswer[$this->questionModel->slug])) {
                throw new QuestionValidation(trans('questionnaire.validation.answer.value', locale: $this->locale));
            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function validateOverWeb(string|array $answer): bool
    {
        try {
            if (array_unique(array_diff(array_keys($answer), array_keys($this->getVariations()))) !== []) {
                throw new QuestionValidation(trans('questionnaire.validation.answer.value', locale: $this->locale));
            }
        } catch (QuestionValidation $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function reformatAnswerFromApi(string $answer): string
    {
        // the only thing that should be reformatted is the answer with key OTHER as it may contain special symbols
        return sanitize_string($answer);
    }

    public function reformatAnswerFromWeb(null|string|array $answer): string|array
    {
        if (is_null($answer)) {
            return [];
        }
        // It's important to sanitize only OTHER option as it may contain special symbols
        if (is_array($answer) && isset($answer[self::OTHER_OPTION_SLUG])) {
            $answer[self::OTHER_OPTION_SLUG] = sanitize_string($answer[self::OTHER_OPTION_SLUG]);
        }
        return $answer;
    }
}
