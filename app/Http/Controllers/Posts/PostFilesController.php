<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\{BinaryFileResponse, StreamedResponse};

/**
 * Controller for user post images.
 *
 * User images are considered to be private, so this controller checks whether requested image belong to user/
 * In case of failure, it returns a placeholder image.
 *
 * @note Must be guarded by auth middleware.
 *
 * @package App\Http\Controllers
 */
final class PostFilesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $postId
     * @param string|null $style
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request, int $postId, ?string $style = null): StreamedResponse|BinaryFileResponse
    {
        try {
            $model = Post::findOrFail($postId);
        } catch (ModelNotFoundException) {
            return response()->streamDownload(
                function () {
                    echo Http::get(config('stapler.api_url') . '/160/00a65a/ffffff/?text=P')->body();
                },
                'image.png',
                [
                    'Content-Type'        => 'image/png',
                    'Content-Disposition' => 'attachment; filename=image.png'
                ]
            );
        }

        abort_if($request->user()?->id !== $model->user_id, Response::HTTP_FORBIDDEN, 'Forbidden');

        $style = match ($style) {
            'thumb' => 'thumb',
            default => 'medium'
        };
        return response()->file($model->image->path($style));
    }
}
