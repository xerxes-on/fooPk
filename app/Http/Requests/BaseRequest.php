<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base for API requests.
 *
 * @package App\Http\Requests
 */
abstract class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if (!$this->expectsJson()) {
            parent::failedValidation($validator);
        }

        $message = (method_exists($this, 'message'))
            ? $this->container->call([$this, 'message'])
            : 'The given data was invalid.';

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    protected function failedAuthorization(): void
    {
        if (!$this->expectsJson()) {
            parent::failedAuthorization();
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
                'errors'  => null,
            ], Response::HTTP_FORBIDDEN)
        );
    }
}
