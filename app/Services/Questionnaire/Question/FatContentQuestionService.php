<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

/**
 * Service responsible for handling question related to clients fat content.
 *
 * @package App\Services\Questionnaire\Question
 */
final class FatContentQuestionService extends BaseValidationRequireQuestionService
{
    public function getVariations(): array
    {
        return [
            '<15%',
            '16_20%',
            '21_30%',
            '>31%',
        ];
    }
}
