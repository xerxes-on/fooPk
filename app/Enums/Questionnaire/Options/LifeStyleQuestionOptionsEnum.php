<?php

declare(strict_types=1);

namespace App\Enums\Questionnaire\Options;

/**
 * Enum determining Questionnaire Question LifeStyle
 *
 * @package App\Enums\Questionnaire
 */
enum LifeStyleQuestionOptionsEnum: string
{
    public const MAINLY_LYING     = 'mainly_lying';
    public const MAINLY_SITTING   = 'mainly_sitting';
    public const SITTING_STANDING = 'sitting_standing';
    public const STANDING_WAKING  = 'standing_waking';
    public const ACTIVE           = 'active';
}
