<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Controller;
use App\Models\UserDashboard;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Modules\Course\Service\WpApi;

/**
 * Dashboard controller
 *
 * @package App\Http\Controllers
 */
final class DashboardController extends Controller
{
    /*
    * Show dashboard
    */
    public function index(): Factory|View
    {
        \Cookie::queue(cookie()->forever(LoginController::AUTHORIZED_COOKIE, (string)time()));
        $now     = Carbon::now()->startOfDay();
        $user    = \Auth::user();
        $recipes = $user
            ->recipes()
            ->with('favorite')
            ->whereDate('recipes_to_users.meal_date', $now)
            ->get();
        $custom = $user
            ->datedCustomRecipes()
            ->whereDate('recipes_to_users.meal_date', $now)
            ->get();
        $recipes = $recipes->merge($custom);
        $course  = $user
            ->courses()
            ->orderBy('pivot_ends_at', 'desc')
            ->first();
        $courseArticle = null;

        if (!is_null($course)) {
            $courseActiveDays = $course->getActiveDays();
            $courseArticle    = $course
                ->articles()
                ->where('days', $courseActiveDays)
                ->first();

            if (!is_null($courseArticle)) {
                $aboArticleID      = $courseArticle->wp_article_id;
                $aboArticleContent = WpApi::getPosts([$aboArticleID]);

                if (!is_null($aboArticleContent)) {
                    $courseArticle         = $aboArticleContent->get($aboArticleID);
                    $courseArticle['days'] = $courseActiveDays;
                }
            }
        }

        $userDashboard = UserDashboard::first();
        $message       = !is_null($userDashboard) ? $userDashboard->message : null;

        $customArticle = null;
        if (!is_null($userDashboard) && !is_null($userDashboard->wp_article_id)) {
            $customArticle = WpApi::getPost((int)$userDashboard->wp_article_id);
        }

        return view('dashboard.dashboard', compact('recipes', 'courseArticle', 'message', 'customArticle'));
    }
}
