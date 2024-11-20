<?php

namespace App\Services\Mails;

use App\Models\User;
use App\Services\Users\UserRecipeCalculationService;
use App\Services\Users\UsersWithInvalidRecipeService;

class MailService
{
    public function __construct(
        public UserRecipeCalculationService  $userRecipeCalculationService,
        public UsersWithInvalidRecipeService $usersWithInvalidRecipeService
    ) {
    }

    public function sendRawAdminEmail(string $email, int $id): void
    {
        send_raw_admin_email(
            "User {$email} (#{$id}) has changed the formular and now they got less than 30 Valid Recipes!",
            'Less than 30 Valid Recipes'
        );
    }

    public function sendRawAdminEmailOnInvalidRecipes(User $user): void
    {
        if (!$this->userRecipeCalculationService->checkIfUserRecipesCountIsValid($user->id)) {
            if (!$this->usersWithInvalidRecipeService->checkIfUserNotified($user->id)) {
                $this->usersWithInvalidRecipeService->createUserWithInvalidRecipe($user->id);
                $this->sendRawAdminEmail($user->email, $user->id);
            }
        } else {
            $this->usersWithInvalidRecipeService->deleteUserFromInvalidRecipe($user->id);
        }
    }
}
