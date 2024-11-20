<?php

namespace App\Services\Users;

use App\Enums\MealtimeEnum;
use App\Enums\Questionnaire\Options\MealPerDayQuestionOptionsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Helpers\Calculation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing calculated user recipes.
 *
 * @package App\Services\Users
 */
class UserRecipeCalculationService
{
    // minimum amount of recipes per ingestion
    public const MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION = 15;

    /**
     * Count calculated and valid user recipes.
     *
     * @param int $userId User id.
     *
     * @return int
     */
    public function getUserRecipesValidCount(int $userId): int
    {
        return DB::table('user_recipe')
            ->where('user_recipe.user_id', $userId)
            ->where('user_recipe.visible', 1)
            ->whereIn('user_recipe.recipe_id', function($query) use($userId){
                return $query->select('recipe_id')
                    ->distinct()
                    ->from('user_recipe_calculated')
                    ->where('user_recipe_calculated.user_id', $userId)
                    ->where('user_recipe_calculated.invalid', 0);

            })
            ->count();
    }
    public function checkIfUserRecipesCountIsValid(int $userId): bool
    {
        return $this->getUserRecipesValidCount($userId) > config('foodpunk.users.min_amount_of_user_recipes');
    }

    public function getUserRecipesValidByIngestionCount(User $user, $ingestionId): int
    {

        return $user->allRecipes()
            ->whereIn('user_recipe.recipe_id', function($query) use($user,$ingestionId){
            return $query->select('recipe_id')
                ->distinct()
                ->from('user_recipe_calculated')
                ->where('user_recipe_calculated.user_id', $user->id)
                ->where('user_recipe_calculated.invalid', 0)
                ->where('user_recipe_calculated.ingestion_id', $ingestionId);
        })->where('visible', 1)->whereHas(
            'ingestions',
            function ($query) use ($ingestionId) {
                $query->where('ingestions.id', $ingestionId);
            }
        )->count();
    }

    public function processMealPerDayChanges($userId)
    {
        $user = User::findOrFail($userId);

        $latestQuestionnaireAnswers = $user->latestQuestionnaireAnswers;

        if (!empty($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])) {
            $ingestionsConfig = match ($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY]) {
                MealPerDayQuestionOptionsEnum::BREAKFAST_LUNCH->value => [
                    MealtimeEnum::BREAKFAST->value => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION,
                    MealtimeEnum::LUNCH->value     => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION
                ],
                MealPerDayQuestionOptionsEnum::BREAKFAST_DINNER->value => [
                    MealtimeEnum::BREAKFAST->value => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION,
                    MealtimeEnum::DINNER->value    => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION
                ],
                MealPerDayQuestionOptionsEnum::LUNCH_DINNER->value => [
                    MealtimeEnum::LUNCH->value  => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION,
                    MealtimeEnum::DINNER->value => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION
                ],
                default => [
                    MealtimeEnum::BREAKFAST->value => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION,
                    MealtimeEnum::LUNCH->value     => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION,
                    MealtimeEnum::DINNER->value    => self::MINIMUM_AMOUNT_OF_RECIPES_FOR_INGESTION
                ],
            };

            $requiredToAddRecipes = [];
            $totalAmount          = 0;
            if (!empty($ingestionsConfig)) {
                foreach ($ingestionsConfig as $ingestionId => $minAmount) {
                    $amountOfRecipes = $this->getUserRecipesValidByIngestionCount($user, $ingestionId);
                    if ($amountOfRecipes < $minAmount) {
                        $requiredToAddRecipes[] = [
                            'ingestionId' => $ingestionId,
                            'amount'      => $minAmount - $amountOfRecipes
                        ];
                    }
                }
            }


            foreach ($requiredToAddRecipes as $item) {
                switch ($item['ingestionId']) {
                    case MealtimeEnum::BREAKFAST->value:
                        $options['strict_amounts']['breakfastSnack'] = $item['amount'];
                        break;
                    case MealtimeEnum::LUNCH->value:
                        if (!empty($options['strict_amounts']['lunchDinner'])) {
                            $options['strict_amounts']['lunchDinner'] += $item['amount'];
                        } else {
                            $options['strict_amounts']['lunchDinner'] = $item['amount'];
                        }
                        break;
                    case MealtimeEnum::DINNER->value:
                        if (!empty($options['strict_amounts']['lunchDinner'])) {
                            $options['strict_amounts']['lunchDinner'] += $item['amount'];
                        } else {
                            $options['strict_amounts']['lunchDinner'] = $item['amount'];
                        }
                        break;
                }
                $totalAmount += $item['amount'];
            }

            if (!empty($options)) {
                $options['distribution_mode'] = Calculation::RECIPE_DISTRIBUTION_FROM_TAG_TYPE_STRICT;
                $additionalRecipeDistribution = Calculation::recipeDistributionFirstTime(
                    $user,
                    $totalAmount,
                    null,
                    null,
                    $options
                );
            }

            Calculation::_generate2subscription($user);
        }
    }
}
