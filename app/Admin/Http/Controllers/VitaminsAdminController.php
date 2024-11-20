<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\VitaminFormRequest;
use App\Http\Controllers\Controller;
use App\Models\Vitamin;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for vitamins.
 *
 * @package App\Http\Controllers\Admin
 */
final class VitaminsAdminController extends Controller
{
    /**
     * Store vitamin and add new vitamin to old ingredients
     */
    public function store(VitaminFormRequest $request): RedirectResponse
    {
        // save vitamin
        $vitamin = Vitamin::updateOrCreate(
            ['id' => $request->id],
            ['name' => $request->name]
        );
        $message = 'record_updated_successfully';

        // if it is new vitamin - add to all ingredients
        if (is_null($request->id)) {
            $vitamin->addVitaminToOldIngredients();
            $message = 'record_created_successfully';
        }

        return redirect()
            ->back()
            ->with('success_message', trans('common.' . $message));
    }
}
