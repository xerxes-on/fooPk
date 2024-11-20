<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\API;

use App\Exceptions\PublicException;
use App\Http\Controllers\API\APIBase;
use App\Http\Controllers\PDFController;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{JsonResponse, Request, Response};
use Modules\ShoppingList\Http\Requests\DatePeriodRequest;
use Modules\ShoppingList\Http\Resources\{ShoppingListResource};
use Modules\ShoppingList\Http\Resources\ShoppingListIngredientCategoryResource;
use Modules\ShoppingList\Http\Resources\ShoppingListRecipeFormatterResource;
use Modules\ShoppingList\Services\ShoppingListGeneratorService;
use Modules\ShoppingList\Services\ShoppingListRetrieverService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class responsible for controlling API for Shopping List.
 *
 * @package App\Http\Controllers\API
 */
final class ShoppingListApiController extends APIBase
{
    /**
     * Get current shopping list with ingredients and recipes.
     *
     * @route GET /api/v1/purchases/list
     */
    public function getList(Request $request, ShoppingListRetrieverService $service): JsonResponse
    {
        $list_information = $service->getList($request->user());
        $response         = [
            'list'        => new ShoppingListResource($list_information['list']),
            'ingredients' => ShoppingListIngredientCategoryResource::collection(
                collect($list_information['ingredient_categories'])->values()
            ),
            'recipes' => new ShoppingListRecipeFormatterResource($list_information['recipes']),
        ];

        return $this->sendResponse($response, trans('common.success'));
    }

    /**
     * Generate shopping list for give period.
     *
     * @route POST /api/v1/purchases/list
     */
    public function generateListForPeriod(DatePeriodRequest $request, ShoppingListGeneratorService $service): JsonResponse
    {
        try {
            $service->generate($request->user(), $request->date_start, $request->date_end);
            return $this->sendResponse(true, trans('common.success'));
        } catch (PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        }
    }

    /**
     * Clear current shopping list.
     *
     * @route GET /api/v1/purchases/list/clear
     */
    public function clearList(Request $request): JsonResponse
    {
        return $request->user()->shoppingList()->delete() ?
            $this->sendResponse(true, trans('shopping-list::messages.success.list_was_deleted')) :
            $this->sendError(
                'not_deleted',
                trans('shopping-list::messages.success.list_was_not_deleted'),
                ResponseAlias::HTTP_ACCEPTED
            );
    }

    /**
     * Generate and retrieve list data in PDF file.
     *
     * @route GET /api/v1/purchases/list/pdf
     */
    public function getListInPDF(Request $request, ShoppingListRetrieverService $service): ResponseFactory|Response
    {
        return (new PDFController())->generatePdf(
            $request,
            view('shopping-list::print', $service->getList($request->user()))->render()
        );
    }
}
