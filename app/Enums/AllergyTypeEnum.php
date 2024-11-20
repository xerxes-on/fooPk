<?php

namespace App\Enums;

/**
 * Enum determining available allergies/diseases/bulk exclusions types.
 *
 * @package App\Enums
 */
enum AllergyTypeEnum: int
{
    case ALLERGY = 1;

    case DISEASE = 2;

    case BULK_EXCLUSIONS = 3;
}
