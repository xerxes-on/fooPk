<?php

declare(strict_types=1);

namespace Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Course\Models\CourseArticle;

/**
 * Articles controller.
 *
 * @package Modules\Course\Http\Controllers
 */
final class ArticlesController extends Controller
{
    public function index(Request $request): View
    {
        return view('course::articles.index', ['courseArticles' => CourseArticle::getAllForUserCourses($request->user())]);
    }

    public function show(Request $request, int $id, int $days): View
    {
        $article = CourseArticle::getSpecific($request->user(), $id, $days);
        $title   = empty($article) ? '' : ': ' . sanitize_string(strip_tags($article['post_title']));

        return view('course::articles.show', ['article' => $article, 'title' => $title]);
    }
}
