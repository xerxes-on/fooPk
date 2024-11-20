<?php

declare(strict_types=1);

namespace App\Jobs;

use Modules\Chargebee\Models\ChargebeeSubscription;
use App\Models\{CustomRecipe,
    CustomRecipeCategory,
    DiaryData,
    Favorite,
    Formular,
    Post,
    Questionnaire,
    SurveyAnswer,
    User,
    UserRecipe,
    UserRecipeCalculatedPreliminary,
    UserSubscription};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\{DB, Log};
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\ShoppingList\Models\ShoppingList;

/**
 * Job allowing to delete users with their belongings.
 * TODO: maybe pass it to deleting event of the model? Need details from client, maybe smgth must be exported to admins
 * @package App\Jobs
 */
final class DeleteUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private readonly array $userIds)
    {
        $this->afterCommit = true;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('deletingUsers'))->releaseAfter(180)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $deleted = [];
        foreach ($this->userIds as $userId) {
            $deleted['deleted_records']["user_$userId"] = [
                'user_recipe_calculated_preliminaries' => UserRecipeCalculatedPreliminary::whereUserId(
                    $userId
                )
                    ->delete(),
                'user_recipe_calculated' => DB::table('user_recipe_calculated')
                    ->where('user_id', $userId)
                    ->delete(),
                'user_recipe' => DB::table('user_recipe')
                    ->where('user_id', $userId)
                    ->delete(),
                'recipes_to_users' => UserRecipe::whereUserId($userId)->delete(),
                // Deleted in cascade
                'purchase_lists|purchase_lists_recipes|purchase_lists_ingredients' => ShoppingList::whereUserId($userId)
                    ->delete(),
                'courses_users' => DB::table('course_users')
                    ->where('user_id', $userId)
                    ->delete(),
                'client_notes' => DB::table('client_notes')
                    ->where('client_id', $userId)
                    ->delete(),
                'chargebee_subscriptions' => ChargebeeSubscription::whereUserId($userId)
                    ->orWhere(
                        'assigned_user_id',
                        $userId
                    )
                    ->delete(),
                'custom_recipes' => CustomRecipe::whereUserId($userId)
                    ->delete(),
                'custom_recipe_categories' => CustomRecipeCategory::whereUserId($userId)
                    ->delete(),
                'diary_datas' => DiaryData::whereUserId($userId)->delete(),
                'favorites'   => Favorite::whereUserId($userId)->delete(),
                // Deleted in cascade
                'flexmeal_lists|flexmeal_to_users' => FlexmealLists::whereUserId($userId)
                    ->with('ingredients')
                    ->delete(),
                'survey_answers' => SurveyAnswer::whereUserId($userId)
                    ->delete(),
                'formulars' => Formular::whereUserId($userId)
                    ->delete(),
                'questionnaire' => Questionnaire::whereUserId($userId)
                    ->delete(),
                'personal_access_tokens' => DB::table('personal_access_tokens')
                    ->where('tokenable_id', $userId)
                    ->delete(),
                'posts' => Post::whereUserId($userId)->delete(),

                // Deleted in cascade
                'wallets_v2|transactions' => DB::table('wallets_v2')
                    ->where(
                        'holder_id',
                        $userId
                    )
                    ->delete(),
                'user_bulk_exclusions' => DB::table('user_bulk_exclusions')
                    ->where('user_id', $userId)
                    ->delete(),
                'user_excluded_ingredients' => DB::table('user_excluded_ingredients')
                    ->where('user_id', $userId)
                    ->delete(),
                'user_excluded_recipes' => DB::table('user_excluded_recipes')
                    ->where('user_id', $userId)
                    ->delete(),
                'user_subscription' => UserSubscription::whereUserId($userId)
                    ->delete(),
            ];
        }

        // 7. delete users
        $deleted['deleted_users'] = User::whereIntegerInRaw('id', $this->userIds)->delete();

        Log::channel('deleted_users')->info("User deletion job finished.\n", $deleted);
    }
}
