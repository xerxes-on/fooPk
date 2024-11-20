<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\CanSendJsonResponse;

/**
 * Base for API controllers.
 *
 * @package App\Http\Controllers\API
 */
abstract class APIBase extends Controller
{
    use CanSendJsonResponse;
}
