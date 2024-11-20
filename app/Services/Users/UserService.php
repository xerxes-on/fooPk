<?php

namespace App\Services\Users;

use App\Models\QuestionnaireTemporary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * User service class
 *
 * @package App\Services
 */
final class UserService
{
    public function createNewUser(array $attributes): void
    {
        // create or update user| role must be set after confirming payment
        $user = User::updateOrCreate(['email' => $attributes['email']], $attributes);
        $user->sendEmailVerificationNotification();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createNewUserFromTemporaryQuestionnaire(string $fingerprint): string
    {
        $answers = QuestionnaireTemporary::whereFingerprint($fingerprint)->with('question')->get();
        $email   = $answers->where('question.slug', 'email')->first()?->answer['email'] ?? null;
        if (is_null($email)) {
            throw new InvalidArgumentException('Unable to create user. Email is not provided');
        }
        $firstName      = $answers->where('question.slug', 'first_name')->first()?->answer['first_name'] ?? '';
        $lang           = $answers->unique('lang')->first()?->lang ?? 'de';
        $allowMarketing = $answers->where('question.slug', 'email')->first()?->answer['subscribe_checkbox'] ?? false;

        $this->createNewUser([
            'email'           => $email,
            'first_name'      => $firstName,
            'lang'            => $lang,
            'allow_marketing' => $allowMarketing
        ]);

        return $email;
    }

    /**
     * Add Welcome bonus for new users.
     */
    public function addUserWelcomeBonus(User $user): void
    {
        // Add foodpoints for all new users
        try {
            $user->deposit(config('formular.new_user_foodpoints_bonus'), ['description' => 'Welcome User Bonus']);
        } catch (\Throwable $e) {
            logError($e);
            // notify admin to add coins manually
            send_raw_admin_email(
                "User $user->email (#$user->id) did not receive welcome foodpoints due to error. Please assign manually.",
                'Welcome bonus error for user'
            );
        }
    }

    /**
     * @param \App\Models\User $user
     * @param array $recipeIds
     * TODO: should be moved to separate service like recipes
     */
    public static function syncRelatedRecipesCreateDate(User $user, array $recipeIds = [])
    {
        if (!empty($recipeIds)) {
            $allRecipesIds = $user->allRecipesPure()->whereIn('recipes.id', $recipeIds)->get();

            $relatedRecipes = $allRecipesIds->filter(
                function ($item) {
                    return $item->related_recipes ?? false;
                }
            )->pluck('related_recipes')->collapse()->unique();
            if (!$relatedRecipes->isEmpty()) {
                $relatedRecipesIds = array_map('intval', $relatedRecipes->toArray());
                $relatedRecipesIds = array_merge($relatedRecipesIds, $recipeIds);
                $allRecipesIds     = $user->allRecipesPure()->whereIn('recipes.id', $relatedRecipesIds)->get();
            }
        } else {
            $allRecipesIds = $user->allRecipesPure()->get();
        }

        $recipesInternalData = [];
        foreach ($allRecipesIds as $recipe) {
            $recipesInternalData[$recipe->id] = Carbon::parse($recipe->pivot->created_at);
        }

        $needUpdateRecipes = [];
        foreach ($allRecipesIds as $recipe) {
            $minDate = Carbon::now();
            if (!empty($recipe->related_recipes)) {
                foreach ($recipe->related_recipes as $recipeId) {
                    if (isset($recipesInternalData[$recipeId]) && $recipesInternalData[$recipeId] < $minDate) {
                        $minDate = $recipesInternalData[$recipeId];
                    }
                }

                if ($minDate < $recipesInternalData[$recipe->id]) {
                    $needUpdateRecipes[$recipe->id] = $minDate;
                }
            }
        }

        if (!empty($needUpdateRecipes)) {
            foreach ($needUpdateRecipes as $recipeId => $date) {
                DB::table('user_recipe')
                    ->where('user_id', $user->id)
                    ->where('recipe_id', $recipeId)
                    ->update(['created_at' => $date]);
            }
        }
    }
}
