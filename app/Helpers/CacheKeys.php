<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class responsible for holding cache keys, dynamic and static ones.
 * TODO: maybe replace with enum and remove that one
 * @package App\Helpers
 */
final class CacheKeys
{
    /*-----------------------INGREDIENTS------------------------------*/

    /**
     * All ingredients key.
     */
    public static function allIngredients(): string
    {
        return "all_ingredients";
    }

    /**
     * All ingredients IDs key.
     */
    public static function allIngredientIds(): string
    {
        return "all_ingredient_ids";
    }

    /**
     * Ingredients allowed for user.
     */
    public static function userIngredients(int $userId): string
    {
        return "all_ingredients_for_user_$userId";
    }

    /*-----------------------INGESTIONS------------------------------*/
    public static function allIngestions(): string
    {
        return "all_ingestions";
    }

    public static function allActiveIngestions(): string
    {
        return "all_active_ingestions";
    }

    /*-----------------------CHARGEBEE------------------------------*/

    /**
     * Chargebee Plans key.
     */
    public static function chargebeePlans()
    {
        return 'chargebee-plans';
    }

    /**
     * Chargebee event.
     */
    public static function chargebeeEvent($id): string
    {
        return "chargebee_event_id_$id";
    }

    /**
     * Admin notification for second chargebee subscription key.
     */
    public static function adminNotificationOnSecondChargebeeSubscription(int $userId): string
    {
        return "admins_notified_about_second_active_chargebee_subscription.user.$userId";
    }

    /*-----------------------RECIPES------------------------------*/

    /**
     * Recipes available to but for user key.
     */
    public static function recipesToBuy(int $userId): string
    {
        return "recipes_to_buy_$userId";
    }

    /**
     * user weekly plan.
     */
    public static function userWeeklyPlan(int $userId, int $week): string
    {
        return "recipes_to_eat_for_{$userId}_week_{$week}";
    }

    /**
     * All user Recipes, Ordinary and custom ones.
     */
    public static function allUserRecipes(int $userId): string
    {
        return "all_recipes_for_$userId";
    }

    public static function allRecipeComplexity(): string
    {
        return "all_recipe_complexity";
    }

    public static function allRecipePrice(): string
    {
        return "all_recipe_price";
    }

    /*-----------------------SEASONS------------------------------*/

    /**
     * Recipe seasons.
     */
    public static function seasons(): string
    {
        return 'all_seasons';
    }

    /*-----------------------DIET------------------------------*/

    /**
     * Recipe seasons.
     */
    public static function diets(): string
    {
        return 'all_diets';
    }

    /*-----------------------SHOPPING LIST------------------------------*/

    /**
     * User shopping list ingredients.
     */
    public static function userShoppingListIngredients(int $userId): string
    {
        return "ingredients_for_{$userId}_in_shopping_list";
    }

    /*-----------------------QUESTIONNAIRE------------------------------*/
    public static function userQuestionnaireExists(int $userId): string
    {
        return "user-$userId-questionnaire-exist";
    }

    public static function userCanEditQuestionnaire(int $userId): string
    {
        return "user-$userId-can-edit-questionnaire";
    }

    public static function userExcludedRecipesIds(int $userId): string
    {
        return "user-$userId-recipes-excluded";
    }

    public static function recipesByIngredients(): string
    {
        return "recipes-by-ingredients";
    }

    /*-----------------------COURSES------------------------------*/
    public static function userParticipatedCourses(int $userId): string
    {
        return "user_{$userId}_participated_courses";
    }
}
