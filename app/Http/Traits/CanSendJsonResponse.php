<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait CanSendJsonResponse
{
    /**
     * Send success response.
     */
    protected function sendResponse(mixed $data, ?string $message = null, ?int $status = null): JsonResponse
    {
        return response()->json(
            [
                'success' => true,
                'data'    => $data,
                'message' => is_null($message) ? trans('common.success') : $message,
                'errors'  => null,
            ],
            $status ?? ResponseAlias::HTTP_OK
        );
    }

    /**
     * Send error response.
     */
    protected function sendError(
        null|string|array $errors = null,
        string            $message = '',
        int               $status = ResponseAlias::HTTP_NOT_FOUND
    ): JsonResponse {
        return response()->json(
            [
                'success' => false,
                'data'    => null,
                'message' => $message,
                'errors'  => $errors,
            ],
            $status
        );
    }
}
