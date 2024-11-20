<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire;

use App\Http\Traits\EnumToArray;

/**
 * Enum determining Questionnaire Question statuses.
 *
 * @package App\Enums\Questionnaire
 */
enum QuestionnaireQuestionStatusEnum: int
{
    use EnumToArray;

    public const DEFAULT = self::DRAFT;
    case DISABLED        = 0;
    case ACTIVE          = 1;
    case DRAFT           = 2;
}
