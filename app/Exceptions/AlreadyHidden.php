<?php

namespace App\Exceptions;

use Exception;

/**
 * It should be thrown on attempt to hide a recipe that's already hidden.
 */
class AlreadyHidden extends Exception
{
}
