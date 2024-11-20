<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\WEB;

use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Modules\ShoppingList\Http\Requests\DatePeriodRequest;
use Modules\ShoppingList\Services\ShoppingListGeneratorService;
use Modules\ShoppingList\Services\ShoppingListRetrieverService;

/**
 * Shopping list controller
 *
 * @package Modules\ShoppingList\Http\Controllers\WEB
 */
final class ShoppingListController extends Controller
{
    /**
     * Get purchases list.
     *
     * @route GET /user/purchases/list
     */
    public function index(Request $request, ShoppingListRetrieverService $service): View|Factory
    {
        return view('shopping-list::index', $service->getList($request->user()));
    }

    /**
     * Create purchase list by dates range.
     *
     * @route POST /user/purchases/list
     */
    public function createListByDates(DatePeriodRequest $request, ShoppingListGeneratorService $service): RedirectResponse
    {
        try {
            $service->generate($request->user(), $request->date_start, $request->date_end);
            return redirect()->back()->with('success', trans('shopping-list::messages.success.list_was_created'));
        } catch (PublicException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clear current purchase list.
     *
     * @route POST /user/purchases/list/clear
     */
    public function clearList(Request $request): RedirectResponse
    {
        return $request->user()->shoppingList()->delete() ?
            redirect()->back()->with('success', trans('shopping-list::messages.success.list_was_deleted')) :
            redirect()->back()->with('error', trans('shopping-list::messages.success.list_was_not_deleted'));
    }

    /**
     * Print active list.
     *
     * @route GET /user/purchases/list/print
     */
    public function printList(Request $request, ShoppingListRetrieverService $service): View|Factory
    {
        return view('shopping-list::print', $service->getList($request->user()));
    }
}
