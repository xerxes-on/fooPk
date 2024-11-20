<?php

declare(strict_types=1);

namespace Modules\Ingredient\Services;

use App\Models\User;

/**
 * Service for syncing user prohibited ingredients.
 *
 * @package Modules\Ingredient\Services
 */
class UserProhibitedIngredientsSyncService
{
    public function syncForbidden(User $user, array $ingredients = []): void
    {
        $user->prohibitedIngredients()->sync($ingredients);
    }
}
