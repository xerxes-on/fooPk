<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use App\Http\Resources\CategoryPreview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ingredient\Http\Resources\IngredientResource;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;

/**
 * API Controller for user ingredients.
 *
 * @package Modules\Ingredient\Http\Controllers\API
 */
final class IngredientsApiController extends APIBase
{
    /**
     * Get ingredients suitable for current user.
     *
     * @route GET /api/v1/ingredients
     */
    public function getForCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->isQuestionnaireExist()) {
            return $this->sendError(message: trans('api.ingredients_no_formular'));
        }

        return $this->sendResponse(IngredientResource::collection($user->getAllowedIngredients()));
    }

    /**
     * Get all ingredients.
     *
     * @route GET /api/v1/all-ingredients
     */
    public function getAll(): JsonResponse
    {
        return $this->sendResponse(IngredientResource::collection(Ingredient::getAll()));
    }

    /**
     * Get main ingredient categories.
     *
     * @route GET /api/v1/main-ingredients
     */
    public function getMainCategories(): JsonResponse
    {
        return $this->sendResponse(CategoryPreview::collection(IngredientCategory::ofAllCategories()->get()));
    }
}
