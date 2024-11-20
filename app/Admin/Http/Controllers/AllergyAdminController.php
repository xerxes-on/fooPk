<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\AllergyFormRequest;
use App\Http\Controllers\Controller;
use App\Models\Allergy;
use App\Models\AllergyTypes;
use Illuminate\Http\RedirectResponse;
use Modules\Ingredient\Jobs\RecalculateUsersForbiddenIngredientsJob;

/**
 * Controller for allergies.
 *
 * @package App\Http\Controllers\Admin
 */
final class AllergyAdminController extends Controller
{
    /**
     * Store allergy
     */
    public function store(AllergyFormRequest $request): RedirectResponse
    {
        $allergyType = AllergyTypes::find($request->get('type_id'));
        $allergy     = Allergy::find($request->get('id'));
        if (is_null($allergy)) {
            $allergy = new Allergy();
        }

        $allergy->name = $request->get('name');
        $allergy->slug = $request->get('slug');
        $allergy->type()->associate($allergyType);
        $allergy->save();

        $allergy->saveIngredientCategories($request->get('categories'));
        $allergy->saveAllowedDiets($request->get('allowedDiets'));
        $allergy->saveIngredients($request->get('ingredients'));

        $message = is_null($request->get('id')) ?
            trans('common.record_created_successfully') :
            trans('common.record_updated_successfully');

        RecalculateUsersForbiddenIngredientsJob::dispatch();
        return redirect()
            ->back()
            ->with('success_message', $message);
    }
}
