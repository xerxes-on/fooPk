<?php

namespace App\Enums\Admin\Permission;

/**
 * Enum determining various users role
 *
 * @package App\Enums\Admin\Permission
 */
enum RoleEnum: string
{
    public const ADMIN_GUARD  = 'admin';
    public const PUBLIC_GUARD = 'web';

    case ADMIN = 'admin';

    case AUTHOR_OF_RECIPES = 'author_of_recipes';

    case CUSTOMER_SUPPORT = 'customer_support';

    case CONSULTANT = 'consultant';

    case USER = 'user';

    case TEST_USER = 'test_user';

    public static function listRoles(): array
    {
        $roles = [];
        foreach (self::cases() as $case) {
            $roles[] = [
                'name'       => $case->value,
                'guard_name' => $case->getGuardName(),
            ];
        }
        return $roles;
    }

    public function getGuardName(): string
    {
        return match ($this) {
            self::ADMIN, self::CUSTOMER_SUPPORT, self::AUTHOR_OF_RECIPES, self::CONSULTANT => self::ADMIN_GUARD,
            default => self::PUBLIC_GUARD
        };
    }

    public static function getAdminRoles(): array
    {
        return [
            self::ADMIN->value,
            self::AUTHOR_OF_RECIPES->value,
            self::CUSTOMER_SUPPORT->value,
            self::CONSULTANT->value,
        ];
    }

    public function getPublicRoles(): array
    {
        return [self::USER];
    }

    public function getPermission(): array
    {
        return match ($this) {
            self::ADMIN             => PermissionEnum::values(),
            self::AUTHOR_OF_RECIPES => [
                // Recipes
                PermissionEnum::RECIPES_MENU->value,
                PermissionEnum::SEE_ALL_RECIPES->value,
                PermissionEnum::CREATE_RECIPE->value,
                PermissionEnum::IMPORT_RECIPE->value,
                PermissionEnum::SEE_RECIPE_TAGS->value,
                PermissionEnum::CREATE_RECIPE_TAGS->value,
                PermissionEnum::RECIPE_DISTRIBUTION->value,
                // Ingredients
                PermissionEnum::INGREDIENTS_MENU->value,
                PermissionEnum::SEE_ALL_INGREDIENTS->value,
                PermissionEnum::CREATE_INGREDIENT->value,
                PermissionEnum::SEE_INGREDIENT_CATEGORIES->value,
                PermissionEnum::SEE_INGREDIENT_TAGS->value,
                PermissionEnum::CREATE_INGREDIENT_TAGS->value,
                PermissionEnum::SEE_INGREDIENT_DIETS->value,
                PermissionEnum::IMPORT_INGREDIENT->value,
                // Users
                PermissionEnum::USER_MENU->value,
                // Media Library
                PermissionEnum::MEDIA_LIBRARY_MENU->value,
                PermissionEnum::SEE_ALL_MEDIA_LIBRARY->value,
                PermissionEnum::IMPORT_MEDIA_LIBRARY->value,
                // Inventory
                PermissionEnum::SEE_ALL_INVENTORY->value,
                PermissionEnum::CREATE_INVENTORY->value
            ],
            self::CUSTOMER_SUPPORT => [
                // Recipes
                PermissionEnum::RECIPES_MENU->value,
                PermissionEnum::SEE_ALL_RECIPES->value,
                PermissionEnum::CREATE_RECIPE->value,
                PermissionEnum::IMPORT_RECIPE->value,
                PermissionEnum::SEE_RECIPE_TAGS->value,
                PermissionEnum::CREATE_RECIPE_TAGS->value,
                PermissionEnum::RECIPE_DISTRIBUTION->value,
                // Ingredients
                PermissionEnum::INGREDIENTS_MENU->value,
                PermissionEnum::SEE_ALL_INGREDIENTS->value,
                PermissionEnum::SEE_INGREDIENT_CATEGORIES->value,
                PermissionEnum::SEE_INGREDIENT_DIETS->value,
                // Users
                PermissionEnum::USER_MENU->value,
                PermissionEnum::SEE_ALL_CLIENTS->value,
                PermissionEnum::CREATE_CLIENT->value,
                PermissionEnum::ADD_RECIPES_TO_CLIENT->value,
                PermissionEnum::DELETE_ALL_USER_RECIPES->value,
                PermissionEnum::MANAGE_CLIENT_BALANCE->value,
                PermissionEnum::MANAGE_SUBSCRIPTION->value,
                PermissionEnum::DELETE_SUBSCRIPTION->value,
                PermissionEnum::DELETE_CLIENT_CHALLENGES->value,
                PermissionEnum::SEE_ALL_ADMINS->value,
                PermissionEnum::MANAGE_CLIENT_FORMULAR->value,
                // Inventory
                PermissionEnum::SEE_ALL_INVENTORY->value,
                // Diseases & Allergies
                PermissionEnum::SEE_ALL_DISEASES->value,
                // Challenges
                PermissionEnum::SEE_ALL_CHALLENGES->value,
                // Notifications
                PermissionEnum::NOTIFICATIONS_MENU->value,
                PermissionEnum::SEE_ALL_NOTIFICATIONS->value,
                PermissionEnum::SEE_ALL_NOTIFICATION_TYPES->value,
            ],
            self::CONSULTANT => [
                // Users
                PermissionEnum::USER_MENU->value,
                PermissionEnum::SEE_ALL_CLIENTS->value,
                PermissionEnum::CREATE_CLIENT->value,
                PermissionEnum::ADD_RECIPES_TO_CLIENT->value,
                PermissionEnum::MANAGE_CLIENT_FORMULAR->value,
                PermissionEnum::DELETE_ALL_USER_RECIPES->value,
                PermissionEnum::MANAGE_SUBSCRIPTION->value,
            ],
            default => []
        };
    }
}
