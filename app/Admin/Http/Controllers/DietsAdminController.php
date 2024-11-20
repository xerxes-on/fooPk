<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\DietFormRequest;
use App\Http\Controllers\Controller;
use App\Models\Diet;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for vitamins.
 *
 * @package App\Http\Controllers\Admin
 */
final class DietsAdminController extends Controller
{
    /**
     * Store vitamin and add new vitamin to old ingredients
     */
    public function store(DietFormRequest $request): RedirectResponse
    {
        Diet::create($request->validated());
        return redirect()
            ->back()
            ->with('success_message', trans('common.record_created_successfully'));
    }
}
