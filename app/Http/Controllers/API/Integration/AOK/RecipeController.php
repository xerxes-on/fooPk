<?php

namespace App\Http\Controllers\API\Integration\AOK;

use App\Http\Controllers\API\APIBase;
use App\Http\Traits\API\Integration\CanGetRecipesForExternalAPI;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schemas (
 *    @OA\Schema(
 *        schema="Recipe",
 *        type="object",
 *        @OA\Property(
 *            property="id",
 *            type="integer",
 *            example="204"
 *        ),
 *        @OA\Property(
 *            property="title",
 *            type="string",
 *            example="Schoko-Rührei mit Heidelbeeren"
 *        ),
 *        @OA\Property(
 *            property="image",
 *            type="string",
 *            example="https://meinplan.foodpunk.de/uploads/recipe/204/original/e20b9936470f4dbd763b5848e276c62f.jpeg"
 *         ),
 *        @OA\Property(
 *            property="meal_time",
 *            type="array",
 *            @OA\Items(
 *                ref="#/components/schemas/MealType"
 *            )
 *         ),
 *        @OA\Property(
 *            property="complexity",
 *            type="object",
 *            @OA\Property(
 *                property="title",
 *                type="string",
 *                example="Einfach"
 *            ),
 *         ),
 *        @OA\Property(
 *            property="price",
 *            type="object",
 *            @OA\Property(
 *                property="title",
 *                type="string",
 *                example="$"
 *            ),
 *            @OA\Property(
 *                property="min_price",
 *                type="number",
 *                example="1"
 *            ),
 *            @OA\Property(
 *                property="max_price",
 *                type="number",
 *                example="10"
 *            ),
 *         ),
 *        @OA\Property(
 *            property="cooking_time",
 *            type="object",
 *            @OA\Property(
 *                property="value",
 *                type="number",
 *                example="35"
 *            ),
 *            @OA\Property(
 *                property="unit",
 *                type="string",
 *                example="minutes"
 *            ),
 *         ),
 *        @OA\Property(
 *            property="diets",
 *            type="array",
 *            @OA\Items(
 *                ref="#/components/schemas/Diet"
 *            )
 *         ),
 *        @OA\Property(
 *            property="seasons",
 *            type="array",
 *            @OA\Items(
 *                type="string",
 *                example="Beliebig"
 *            )
 *         ),
 *        @OA\Property(
 *            property="steps",
 *            type="array",
 *            @OA\Items(
 *                ref="#/components/schemas/Step"
 *            )
 *         ),
 *    ),
 *    @OA\Schema(
 *        schema="MealType",
 *        type="object",
 *        @OA\Property(
 *            property="title",
 *            type="string",
 *            example="Mittagessen"
 *         ),
 *        @OA\Property(
 *            property="key",
 *            type="string",
 *            example="lunch"
 *         ),
 *        @OA\Property(
 *            property="ingredients",
 *            type="array",
 *            @OA\Items(
 *                ref="#/components/schemas/Ingredient"
 *            )
 *         ),
 *        @OA\Property(
 *            property="nutrition",
 *            type="object",
 *            @OA\Property(
 *                property="KH",
 *                type="string",
 *                example="20"
 *            ),
 *            @OA\Property(
 *                property="EW",
 *                type="string",
 *                example="18.3"
 *            ),
 *            @OA\Property(
 *                property="F",
 *                type="string",
 *                example="24.9"
 *            ),
 *            @OA\Property(
 *                property="KCal",
 *                type="string",
 *                example="393"
 *            )
 *         ),
 *    ),
 *    @OA\Schema(
 *        schema="Diet",
 *        type="object",
 *        @OA\Property(
 *            property="name",
 *            type="string",
 *            example="Vegetarisch"
 *         )
 *    ),
 *    @OA\Schema(
 *        schema="Ingredient",
 *        type="object",
 *        @OA\Property(
 *            property="amount",
 *            type="number",
 *            example="15"
 *         ),
 *        @OA\Property(
 *            property="name",
 *            type="string",
 *            example="pasture butter"
 *         ),
 *         @OA\Property(
 *            property="unit",
 *            type="string",
 *            example="g"
 *         )
 *    ),
 *    @OA\Schema(
 *        schema="Step",
 *        type="object",
 *        @OA\Property(
 *            property="description",
 *            type="string",
 *            example="Some long description"
 *         )
 *    )
 * )
 *
 *
 * @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    type="http",
 *    scheme="Bearer",
 *    in="header"
 * )
 */
class RecipeController extends APIBase
{
    use CanGetRecipesForExternalAPI;

    /**
     * @OA\Get (
     *     path="/api/v1/aok/get-recipes-data",
     *     summary="Get recipes data",
     *     description="Returns all receipts for a user",
     *     operationId="get-recipes-data",
     *     tags={"Foodpunk"},
     *     security={
     *          {"bearerAuth": {}}
     *      },
     *     @OA\Response(
     *          response="200",
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Recipe"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Erfolg"
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="integer",
     *                  nullable=true,
     *                  example="null"
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Unauthenticated."
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Recipes not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="number",
     *                  nullable=true,
     *                  example="null"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="string",
     *                  example="Dieses Rezept ist für deinen aktuellen Plan leider nicht verfügbar."
     *              ),
     *         )
     *     )
     * )
     *
     */
    public function getAvailableRecipes(): JsonResponse
    {
        return $this->getRecipesData();
    }
}
