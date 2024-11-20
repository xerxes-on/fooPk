<?php

namespace App\Http\Controllers\API\Integration\Apinity;

use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Traits\API\Integration\CanLoginViaExternalAPI;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Foodpunk API",
 *     version="1.00",
 *     description="Foodpunk OpenApi designated documentation"
 * )
 *
 *
 * @OA\Server(
 *     description="Main API server",
 *     url="https://meinplan.foodpunk.de"
 * )
 *
 * @OA\Tag(
 *     name="Foodpunk API",
 *     description="API Endpoints for external integration"
 * )
 *
 * @OA\Components(
 *     @OA\RequestBody(
 *         request="authBody",
 *         description="A JSON object containing user credentials",
 *         required=true,
 *         @OA\JsonContent(
 *              @OA\Property(
 *                  property="email",
 *                  type="string",
 *                  example="username@foodpunk.de"
 *             ),
 *            @OA\Property(
 *                  property="password",
 *                  type="string",
 *                  example="generatedpassword"
 *             )
 *         )
 *     )
 * )
 *
 */
final class AuthController extends APIBase
{
    use CanLoginViaExternalAPI;

    public function __construct()
    {
        $this->abilityName = 'apinity-integration';
    }

    /**
     * @OA\Post(
     *     path="/api/v1/apinity/auth",
     *     summary="Endpoint for authentication through OpenAPI",
     *     description="Returns a temporary authentication token",
     *     operationId="auth-apinity",
     *     tags={"Foodpunk API"},
     *     @OA\RequestBody(
     *       ref="#/components/requestBodies/authBody"
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Authentication response",
     *        @OA\JsonContent(
     *            @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="token",
     *                      type="string",
     *                      example="1234|0OYMD4iyyuNQriiUVR0SZxjCdDO0L8ZuaWfYob6x"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="The sign in is successful."
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="integer",
     *                  nullable=true,
     *                  example="null"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Authentication failed",
     *        @OA\JsonContent(
     *             @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example="false"
     *              ),
     *             @OA\Property(
     *                  property="data",
     *                  type="integer",
     *                  nullable=true,
     *                  example="null"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="The provided credentials are incorrect."
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="integer",
     *                  nullable=true,
     *                  example="null"
     *              )
     *          )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->handleLogin($request);
    }
}
