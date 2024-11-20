<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire;

use App\Http\Traits\EnumToArray;

/**
 * Enum determining Questionnaire Question types.
 *
 * @package App\Enums\Questionnaire
 */
enum QuestionnaireQuestionTypesEnum: int
{
    use EnumToArray;

    case RADIO         = 1;
    case NUMBER        = 2;
    case CHECKBOX      = 3;
    case TEXT          = 4;
    case INFO_PAGE     = 5;
    case SALES_PAGE    = 6;
    case MIXED         = 7;
    case SEARCH_SELECT = 8;
    case DATE          = 9;
}
