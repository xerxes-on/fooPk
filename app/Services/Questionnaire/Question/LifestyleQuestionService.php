<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Enums\Questionnaire\Options\LifeStyleQuestionOptionsEnum;

/**
 * Service responsible for handling question related to client lifestyle.
 *
 * @package App\Services\Questionnaire\Question
 */
final class LifestyleQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            LifeStyleQuestionOptionsEnum::MAINLY_LYING,
            LifeStyleQuestionOptionsEnum::MAINLY_SITTING,
            LifeStyleQuestionOptionsEnum::SITTING_STANDING,
            LifeStyleQuestionOptionsEnum::STANDING_WAKING,
            LifeStyleQuestionOptionsEnum::ACTIVE
        ];
    }
}
