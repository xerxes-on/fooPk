<?php

declare(strict_types=1);

namespace Modules\Course\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Modules\Course\Http\Requests\Admin\ArticleToCourseAttachmentRequest;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseArticle;
use Modules\Course\Service\WpApi;

/**
 * Controller for ABO challenges.
 *
 * @package App\Http\Controllers\Admin
 */
final class CourseAdminController extends Controller
{
    public function attachArticleToCourse(ArticleToCourseAttachmentRequest $request, int $id): RedirectResponse
    {
        $aboChallenge = Course::findOrFail($id);

        $article = new CourseArticle($request->only(['wp_article_id', 'days']));
        $aboChallenge->articles()->save($article);

        return redirect()
            ->back()
            ->with('success_message', trans('Article successfully added.'));
    }

    public function find(Request $request): Collection
    {
        return WpApi::searchPosts($request->get('search'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $aboChallenge = Course::findOrFail($request->route('id'));
        $aboChallenge->articles()->where($request->only(['wp_article_id', 'days']))->delete();
        return redirect()
            ->back()
            ->with('success_message', trans('common.record_deleted_successfully'));
    }
}
