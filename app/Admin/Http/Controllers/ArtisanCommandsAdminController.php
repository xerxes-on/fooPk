<?php

namespace App\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Artisan;

final class ArtisanCommandsAdminController extends Controller
{
    /**
     * Handle the request about cache cleaning.
     */
    public function actionOptimizeClear(Request $request): RedirectResponse|JsonResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('optimize:clear');

        return $request->expectsJson() ?
            response()->json(['success' => true, 'message' => __('common.system_cache_has_been_cleared')]) :
            back()->with('message', __('common.system_cache_has_been_cleared'));
    }

    /**
     * Temporary handler to export users with lipedema.
     */
    public function exportUsersWithLipedema(): void
    {
        Artisan::call('internal:export-lipedema-users');
    }
}
