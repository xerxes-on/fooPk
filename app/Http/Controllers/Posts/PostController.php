<?php

namespace App\Http\Controllers\Posts;

use App\Exceptions\NoData;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostFormRequest;
use App\Models\Post as PostModel;
use App\Services\Post as PostService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Post controller
 *
 * @package App\Http\Controllers
 */
final class PostController extends Controller
{
    /**
     * List posts.
     */
    public function index(PostService $service): View|Factory
    {
        return view('post.list', ['posts' => $service->getAll()]);
    }

    /**
     * Create a post.
     *
     * @route POST /user/post
     */
    public function store(PostFormRequest $request, PostService $service): RedirectResponse
    {
        $service->processStore((array)$request->validated());
        return redirect('user/posts')->with('success', trans('common.record_created_successfully'));
    }

    /**
     * Get dairy dates post form.
     */
    public function getPostForm(Request $request): Factory|View|JsonResponse
    {
        $postId = $request->get('postId');
        $user   = $request->user();
        if (empty($postId)) {
            $post = new PostModel();
            $view = 'post.create';
        } else {
            $post = $user->posts()->find($postId);
            $view = 'post.edit';
        }

        $diaryData = $user?->diaryDates()?->get();

        if ($request->ajax()) {
            return response()->json(
                [
                    'success' => true,
                    'payload' => view($view, compact('post', 'diaryData'))->render(),
                ]
            );
        }

        return view('post.create', compact('post', 'diaryData'));
    }

    /**
     * Save a post.
     */
    public function update(PostFormRequest $request, int $id, PostService $service): RedirectResponse
    {
        try {
            $service->processUpdate((array)$request->validated(), $id);
            return redirect('user/posts')->with('success', trans('common.record_updated_successfully'));
        } catch (NoData $e) {
            return redirect('user/posts')->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a post.
     */
    public function destroy(int $id, PostService $service): RedirectResponse
    {
        try {
            $service->processDestroy($id);
            return redirect('user/posts')->with('success', trans('common.record_deleted_successfully'));
        } catch (NoData $e) {
            return redirect('user/posts')->with('error', $e->getMessage());
        }
    }
}
