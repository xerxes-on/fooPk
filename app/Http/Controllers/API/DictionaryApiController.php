<?php

namespace App\Http\Controllers\API;

use App\Exceptions;
use App\Http\Requests\API\TranslationsPayload;
use App\Repositories\Dictionaries;
use Illuminate\Http\{JsonResponse, Response};

/**
 * Controller that handles mobile dictionary API.
 *
 * @package App\Http\Controllers\API
 */
final class DictionaryApiController extends APIBase
{
    public function __construct(private Dictionaries $dictionariesRepo)
    {
    }

    /**
     * Send mobile dictionary data.
     *
     * @route GET /api/v1/dictionary/
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        return $this->sendResponse(
            [
                'current_language' => auth('sanctum')->user()?->lang ?? 'de',
                'en'               => $this->dictionariesRepo->get('mobile_app', 'en'),
                'de'               => $this->dictionariesRepo->get('mobile_app', 'de'),
            ],
            'Do not forget to cache data.'
        );
    }

    /**
     * Upload new dictionary.
     *
     * @route POST /api/v1/dictionary/
     *
     * @param \App\Http\Requests\API\TranslationsPayload $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(TranslationsPayload $request): JsonResponse
    {
        if ('production' === config('app.env')) {
            return $this->sendError(message: 'Action isn\'t allowed', status: Response::HTTP_METHOD_NOT_ALLOWED);
        }

        try {
            $overrides = $this->dictionariesRepo->override($request->translations, $request->language, 'mobile_app');
        } catch (Exceptions\PublicException $exception) {
            return $this->sendError(message: $exception->getMessage());
        }

        return $this->sendResponse($overrides, trans('common.success'));
    }
}
