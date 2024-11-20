<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Cookies controller
 *
 * @package App\Http\Controllers
 */
class CookieController extends Controller
{
    /**
     * Set cookie
     */
    public function set(Request $request): JsonResponse
    {
        $cookieName = $request->get('cookie_name');
        $cookieVal  = $request->get('cookie_val');
        $cookieExp  = $request->get('expiration');
        Cookie::queue($cookieName, $cookieVal, $cookieExp);
        return response()->json(['success' => true]);
    }
}
