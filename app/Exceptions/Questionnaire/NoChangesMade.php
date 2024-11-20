<?php

namespace App\Exceptions\Questionnaire;

use Exception;

/**
 * Exception with error message meant that new answers are the same as old ones.
 *
 * @package App\Exceptions\Questionnaire
 */
class NoChangesMade extends Exception
{
}
