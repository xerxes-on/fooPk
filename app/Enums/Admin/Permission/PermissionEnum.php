<?php

namespace App\Enums\Admin\Permission;

use App\Http\Traits\EnumToArray;

/**
 * Enum determining various users permissions
 *
 * @package App\Enums\Admin\Permission
 */
enum PermissionEnum: string
{
    use EnumToArray;

    /*-----------------------RECIPES------------------------------*/
    case RECIPES_MENU    = 'recipes';
    case SEE_ALL_RECIPES = 'see_all_recipes';
    case CREATE_RECIPE   = 'create_recipe';
    case IMPORT_RECIPE   = 'import_recipe';
    case DELETE_RECIPE   = 'delete_recipe';

    /*-----------------------RECIPES->TAGS------------------------------*/
    case SEE_RECIPE_TAGS    = 'see_recipe_tags';
    case CREATE_RECIPE_TAGS = 'create_recipe_tags';
    case DELETE_RECIPE_TAGS = 'delete_recipe_tags';

    /*-----------------------RECIPES->DISTRIBUTION------------------------------*/
    case RECIPE_DISTRIBUTION = 'manage_distribution';

    /*-----------------------INGREDIENTS------------------------------*/
    case INGREDIENTS_MENU    = 'ingredients';
    case SEE_ALL_INGREDIENTS = 'see_all_ingredients';
    case CREATE_INGREDIENT   = 'create_ingredient';
    case DELETE_INGREDIENT   = 'delete_ingredient';

    /*-----------------------INGREDIENTS->CATEGORY ------------------------------*/
    case SEE_INGREDIENT_CATEGORIES    = 'see_ingredient_categories';
    case CREATE_INGREDIENT_CATEGORIES = 'create_ingredient_categories';
    case DELETE_INGREDIENT_CATEGORY   = 'delete_ingredient_categories';

    /*-----------------------INGREDIENTS->TAGS ------------------------------*/
    case SEE_INGREDIENT_TAGS    = 'see_ingredient_tags';
    case CREATE_INGREDIENT_TAGS = 'create_ingredient_tags';
    case DELETE_INGREDIENT_TAGS = 'delete_ingredient_tags';

    /*-----------------------INGREDIENTS->DIETS ------------------------------*/
    case SEE_INGREDIENT_DIETS    = 'see_ingredient_diets';
    case CREATE_INGREDIENT_DIETS = 'create_ingredient_diets';
    case DELETE_INGREDIENT_DIETS = 'delete_ingredient_diets';

    /*-----------------------INGREDIENTS->UNITS ------------------------------*/
    case SEE_INGREDIENT_UNITS    = 'see_ingredient_units';
    case CREATE_INGREDIENT_UNITS = 'create_ingredient_units';
    case DELETE_INGREDIENT_UNITS = 'delete_ingredient_units';

    /*-----------------------INGREDIENTS->SEASONS ------------------------------*/
    case SEE_INGREDIENT_SEASONS    = 'see_ingredient_seasons';
    case CREATE_INGREDIENT_SEASONS = 'create_ingredient_seasons';
    case DELETE_INGREDIENT_SEASONS = 'delete_ingredient_seasons';

    /*-----------------------INGREDIENTS->IMPORT ------------------------------*/
    case IMPORT_INGREDIENT = 'import_ingredient';

    /*-----------------------USERS------------------------------*/
    case USER_MENU = 'users';

    /*-----------------------USERS->CLIENTS------------------------------*/
    case SEE_ALL_CLIENTS          = 'see_all_clients';
    case CREATE_CLIENT            = 'create_client';
    case DELETE_CLIENT            = 'delete_client';
    case ADD_RECIPES_TO_CLIENT    = 'add_recipes_to_client';
    case DELETE_ALL_USER_RECIPES  = 'delete_all_user_recipes';
    case MANAGE_CLIENT_BALANCE    = 'manage_client_balance';
    case MANAGE_SUBSCRIPTION      = 'manage_client_subscription';
    case DELETE_SUBSCRIPTION      = 'delete_client_subscription';
    case DELETE_CLIENT_CHALLENGES = 'delete_client_challenges';
    case MANAGE_CLIENT_FORMULAR   = 'manage_client_formular';

    /*-----------------------USERS->ADMIN------------------------------*/
    case SEE_ALL_ADMINS = 'see_all_admins';
    case CREATE_ADMIN   = 'create_admin';
    case DELETE_ADMIN   = 'delete_admin';
    case ANALYTICS      = 'analytics';

    /*-----------------------MEDIA LIBRARY------------------------------*/
    case MEDIA_LIBRARY_MENU    = 'media_library';
    case SEE_ALL_MEDIA_LIBRARY = 'see_all_media_library';
    case IMPORT_MEDIA_LIBRARY  = 'import_media_library';

    /*-----------------------VITAMINS------------------------------*/
    case SEE_ALL_VITAMINS = 'see_all_vitamins';
    case CREATE_VITAMINS  = 'create_vitamins';
    case DELETE_VITAMINS  = 'delete_vitamins';

    /*-----------------------INVENTORY------------------------------*/
    case SEE_ALL_INVENTORY = 'see_all_inventory';
    case CREATE_INVENTORY  = 'create_inventory';
    case DELETE_INVENTORY  = 'delete_inventory';

    /*-----------------------DISEASES & ALLERGY------------------------------*/
    case SEE_ALL_DISEASES = 'see_all_diseases';
    case CREATE_DISEASES  = 'create_diseases';
    case DELETE_DISEASES  = 'delete_diseases';

    /*-----------------------CHALLENGES & ARTICLES------------------------------*/
    case SEE_ALL_CHALLENGES = 'see_all_challenges';
    case CREATE_CHALLENGES  = 'create_challenges';
    case DELETE_CHALLENGES  = 'delete_challenges';

    /*-----------------------SETTINGS------------------------------*/
    case SETTINGS_MENU = 'settings';

    /*-----------------------SETTINGS->RECIPE COMPLEXITY------------------------------*/
    case SEE_ALL_RECIPE_COMPLEXITY = 'see_all_recipe_complexity';
    case CREATE_RECIPE_COMPLEXITY  = 'create_recipe_complexity';
    case DELETE_RECIPE_COMPLEXITY  = 'delete_recipe_complexity';

    /*-----------------------SETTINGS->RECIPE PRICE------------------------------*/
    case SEE_ALL_RECIPE_PRICE = 'see_all_recipe_price';
    case CREATE_RECIPE_PRICE  = 'create_recipe_price';
    case DELETE_RECIPE_PRICE  = 'delete_recipe_price';

    /*-----------------------NOTIFICATIONS------------------------------*/
    case NOTIFICATIONS_MENU     = 'notifications';
    case SEE_ALL_NOTIFICATIONS  = 'see_all_notifications';
    case CREATE_NOTIFICATIONS   = 'create_notifications';
    case DISPATCH_NOTIFICATIONS = 'dispatch_notifications';
    case DELETE_NOTIFICATIONS   = 'delete_notifications';

    /*-----------------------NOTIFICATIONS->TYPE------------------------------*/
    case SEE_ALL_NOTIFICATION_TYPES = 'see_all_notification_types';
    case CREATE_NOTIFICATION_TYPES  = 'create_notification_types';
    case DELETE_NOTIFICATION_TYPES  = 'delete_notification_types';

    /*-----------------------PAGES------------------------------*/
    case SEE_ALL_PAGES = 'see_all_pages';
    case CREATE_PAGES  = 'create_pages';
    case DELETE_PAGES  = 'delete_pages';

    /**
     * @note currently all permissions belong to admin guard by business requirements!
     */
    public static function listPermissions(): array
    {
        $permissions = [];
        foreach (self::cases() as $case) {
            $permissions[] = [
                'name'       => $case->value,
                'guard_name' => RoleEnum::ADMIN_GUARD,
            ];
        }
        return $permissions;
    }
}
