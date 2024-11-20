<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use Throwable;

/**
 * ChooseDevice controller
 *
 * @package App\Http\Controllers
 */
final class ChooseDeviceController extends Controller
{
    /**
     * Show the choose_device page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function __invoke(): Factory|View
    {
        // TODO::hardcode, refactor it also in app/Http/Controllers/Auth/ResetPassword.php

        try {
            $language = (string)session()->get('translatable_lang');
        } catch (Throwable) {
            $language = null;
        }

        if ($language) {
            App::setLocale($language);
        }

        $language = app()->getLocale();
        return view('layouts.choose_device', compact('language'));
    }
}
