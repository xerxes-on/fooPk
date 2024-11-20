<?php

namespace Modules\Internal\Enums;

enum JobProcessingEnum: string
{
    case  INGREDIENT_CATEGORY       = 'processed_categories';
    case AFTER_QUESTIONNAIRE_CHANGE = 'after_formular_change_';
    case PRELIMINARY_JOB            = 'preliminary_job';

    case USER_PROHIBITED_INGREDIENTS = 'user_prohibited_ingredients';
}
