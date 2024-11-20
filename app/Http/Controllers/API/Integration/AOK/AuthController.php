<?php

namespace App\Http\Controllers\API\Integration\AOK;

use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Traits\API\Integration\CanLoginViaExternalAPI;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="AOK API",
 *     version="1.00",
 *     description="AOK API"
 * )
 *
 * @OA\Server(
 *     description="Staging",
 *     url="https://staging.foodpunk.de"
 * )
 *
 * @OA\Server(
 *     description="Production",
 *     url="https://meinplan.foodpunk.de"
 * )
 *
 * @OA\Tag(
 *     name="Foodpunk",
 *     description="API Endfoints for AOK integration"
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
 *                  example="aokmasteruser@foodpunk.de"
 *             ),
 *            @OA\Property(
 *                  property="password",
 *                  type="string",
 *                  example="aokmasteruser"
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
        $this->abilityName = 'aok-integration';
    }

    /**
     * @OA\Post(
     *     path="/api/v1/aok/auth",
     *     summary="User login",
     *     description="Returns a token for authentication",
     *     operationId="auth-AOK",
     *     tags={"Foodpunk"},
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
     *                      example="6693|0OYMD4iyyuNQriiUVR0SZxjCdDO0L8ZuaWfYob6x"
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
