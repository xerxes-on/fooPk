<?php

namespace App\Services;

use App\Exceptions\NoData;
use App\Models\Post as PostModel;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Posts
 *
 * @package App\Repositories
 */
final class Post
{
    private User|Authenticatable $user;

    public function __construct()
    {
        $user = auth()->user();

        if (is_null($user)) {
            $user = auth('sanctum')->user();
        }

        if (is_null($user)) {
            response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN)->send();
            exit(Response::HTTP_FORBIDDEN);
        }

        $this->user = $user;
    }

    /**
     * Retrieve available Posts.
     */
    public function getAll(): Collection
    {
        return $this->user->posts()->orderBy('id', 'desc')->get();
    }

    /**
     * Process post store.
     */
    public function processStore(array $data): void
    {
        $this->user->posts()->save(new PostModel($data));
    }

    /**
     * Process post update.
     *
     * @throws NoData
     */
    public function processUpdate(array $data, int $postId): void
    {
        try {
            /**@var PostModel $post */
            $post = $this->user->posts()->findOrFail($postId);
        } catch (ModelNotFoundException) {
            throw new NoData(trans('common.nothing_found'));
        }

        $imageIsEmpty = empty($data['image']);
        // Check if we need to remove image
        if ($imageIsEmpty) {
            $data['image'] = STAPLER_NULL;
            $post->update($data);
            return;
        }

        // Check if image should be omitted
        $imageFieldsAreFilled = !$imageIsEmpty && !empty($data['oldImage']);
        $sameImageOnUpdate    = $imageFieldsAreFilled && ($post->image_file_name === $data['image']);
        $sameImageOnStore     = $imageFieldsAreFilled && is_object($data['image']) && ($data['oldImage'] === $data['image']?->getClientOriginalName());
        if ($sameImageOnUpdate || $sameImageOnStore) {
            $data = ['content' => $data['content']];
        }

        $post->update($data);
    }

    /**
     * Process post delete.
     *
     * @throws NoData
     */
    public function processDestroy(int $postId): void
    {
        try {
            /**@var PostModel $post */
            $post = $this->user->posts()->findOrFail($postId);
        } catch (ModelNotFoundException) {
            throw new NoData(trans('common.nothing_found'));
        }

        $post->image->destroy();
        $post->delete();
    }
}
