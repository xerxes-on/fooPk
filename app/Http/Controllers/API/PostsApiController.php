<?php

namespace App\Http\Controllers\API;

use App\Exceptions\NoData;
use App\Http\Requests\PostFormRequest;
use App\Http\Resources\Diary\Post as PostResource;
use App\Services\Post as PostService;
use Illuminate\Http\JsonResponse;

/**
 * Api controller of user posts.
 *
 * @package App\Http\Controllers\API
 */
final class PostsApiController extends APIBase
{
    /**
     * Retrieve all available posts.
     *
     * @route  POST /api/v1/posts/get
     */
    public function getAll(PostService $service): JsonResponse
    {
        $collection = $service->getAll();

        return $collection->isEmpty() ?
            $this->sendError(trans('api.no_posts')) :
            $this->sendResponse(PostResource::collection($collection), trans('common.success'));
    }

    /**
     * Store user post.
     *
     * @route  POST /api/v1/posts/store
     */
    public function store(PostFormRequest $request, PostService $service): JsonResponse
    {
        $service->processStore((array)$request->validated());
        return $this->sendResponse(trans('common.record_created_successfully'), trans('common.success'));
    }

    /**
     * Update user post.
     *
     * @route  POST /api/v1/posts/{id}/update
     */
    public function update(PostFormRequest $request, int $id, PostService $service): JsonResponse
    {
        try {
            $service->processUpdate((array)$request->validated(), $id);
            return $this->sendResponse(trans('common.record_updated_successfully'), trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Delete user post.
     *
     * @route  DELETE /api/v1/posts/{id}/delete
     */
    public function destroy(int $id, PostService $service): JsonResponse
    {
        try {
            $service->processDestroy($id);
            return $this->sendResponse(trans('common.record_deleted_successfully'), trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
