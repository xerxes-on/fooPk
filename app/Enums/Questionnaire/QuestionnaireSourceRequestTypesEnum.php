<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire;

/**
 * Enum determining Questionnaire type.
 *
 * @package App\Enums\Questionnaire
 */
enum QuestionnaireSourceRequestTypesEnum: int
{
    case API         = 1;
    case WEB         = 2;
    case API_EDITING = 3;
    case WEB_EDITING = 4;
}
