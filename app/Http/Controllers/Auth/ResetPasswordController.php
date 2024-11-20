<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

/**
 * Controller for resetting password.
 *
 * This controller is responsible for handling password reset requests
 * and uses a simple trait to include this behavior. You're free to
 * explore this trait and override any methods you wish to tweak.
 *
 * @package App\Http\Controllers\Auth
 */
class ResetPasswordController extends Controller
{
    use ResetsPasswords {
        showResetForm as showBaseResetForm;
    }

    /**
     * Where to redirect users after resetting their password.
     */
    protected string $redirectTo = '/user/layouts/choose_device';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Translated version of password reset view.
     *
     * @route GET /password/reset/{token}/{language}
     */
    public function showResetForm(Request $request, string|null $token = null, string $language = 'de'): Factory|View
    {
        // TODO:: hardcode, refactor it also in app/Http/Controllers/ChooseDevice.php
        $wholeRequestArrayKeys = array_keys($request->all());
        if (in_array('en', $wholeRequestArrayKeys)) {
            $language = 'en';
        } elseif (in_array('de', $wholeRequestArrayKeys)) {
            $language = 'de';
        }

        App::setLocale($language);
        session()->put('translatable_lang', $language);
        return $this->showBaseResetForm($request);
    }
}
