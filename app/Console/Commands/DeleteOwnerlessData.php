<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\{CustomRecipe,
    CustomRecipeCategory,
    DiaryData,
    Favorite,
    Post,
    User,
    UserRecipe,
    UserRecipeCalculatedPreliminary,
    UserSubscription
};
use Modules\Chargebee\Models\ChargebeeSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Log};
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\ShoppingList\Models\ShoppingList;

/**
 * Command allowing to find ownerless data across specific table and remove it.
 *
 * @package App\Console\Commands
 */
final class DeleteOwnerlessData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean-ownerless-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the ownerless data across database';

    /**
     * Array of existing user ids.
     */
    private array $existingUserIds = [];

    /**
     * Initial time of the job execution.
     */
    private float $startTimer = 0;

    /**
     * Size of the chunk for the DB record selection.
     */
    private int $chunkSize = 1000;

    /**
     * Size of the chunk for the DB record deletion limit.
     */
    private int $deleteChunkSize = 500;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Job started.\n");
        $this->startTimer = microtime(true);

        $this->existingUserIds = User::pluck('id')->toArray();
        $this->info('User exists: ' . count($this->existingUserIds) . "\n" . 'Start processing records...');

        $data                 = ['tables_processed' => $this->processDelete()];
        $data['timeConsumed'] = $this->getTimeExecutionDiff();

        $this->info('Job finished. Logs were written to the file.');

        Log::channel('deleted_users')->info(self::class . ' job finished. The following records were deleted.' . "\n", $data);

        return self::SUCCESS;
    }

    /**
     * Process deletion of the ownerless data via DB QueryBuilder.
     */
    private function processDeletionViaQueryBuilder(
        string $tableName,
        string $orderBy = 'id',
        array  $select = ['id', 'user_id'],
        string $key = 'id',
        string $where = 'user_id'
    ): array {
        $idsToDelete    = [];
        $deletedRecords = 0;
        DB::table($tableName)
            ->orderBy($orderBy)
            ->select($select)
            ->chunk(
                $this->chunkSize,
                function ($chunk) use (&$idsToDelete, $key, $where) {
                    $idsToDelete = array_merge(
                        $idsToDelete,
                        $chunk->whereNotIn($where, $this->existingUserIds)->pluck($key)->toArray()
                    );
                }
            );

        if ($idsToDelete === []) {
            return [
                'deletedRecords' => $deletedRecords,
                'executionTime'  => $this->getTimeExecutionDiff()
            ];
        }

        if (count($idsToDelete) > $this->deleteChunkSize) {
            foreach (array_chunk($idsToDelete, $this->deleteChunkSize) as $chunk) {
                $deletedRecords += DB::table($tableName)
                    ->whereIntegerInRaw($key, $chunk)
                    ->delete();
            }
            return [
                'deletedRecords' => $deletedRecords,
                'executionTime'  => $this->getTimeExecutionDiff()
            ];
        }

        $deletedRecords += DB::table($tableName)
            ->whereIntegerInRaw($key, $idsToDelete)
            ->delete();
        return [
            'deletedRecords' => $deletedRecords,
            'executionTime'  => $this->getTimeExecutionDiff()
        ];
    }

    /**
     * Process deletion of the ownerless data via Eloquent ORM.
     */
    private function processDeletionViaEloquent(
        string $className,
        string $orderBy = 'id',
        array  $select = ['id', 'user_id'],
        string $key = 'id',
    ): array {
        $idsToDelete    = [];
        $deletedRecords = 0;
        $className::orderBy($orderBy)
            ->select($select)
            ->chunk(
                $this->chunkSize,
                function ($chunk) use (&$idsToDelete, $key) {
                    $idsToDelete = array_merge(
                        $idsToDelete,
                        $chunk->whereNotIn('user_id', $this->existingUserIds)->pluck($key)->toArray()
                    );
                }
            );

        if ($idsToDelete === []) {
            return [
                'deletedRecords' => $deletedRecords,
                'executionTime'  => $this->getTimeExecutionDiff()
            ];
        }

        if (count($idsToDelete) > $this->deleteChunkSize) {
            foreach (array_chunk($idsToDelete, $this->deleteChunkSize) as $chunk) {
                // I do not want to make duplicated code parts some im forced to use this...
                $deletedRecords += match ($className) {
                    ChargebeeSubscription::class => $className::whereIntegerNotInRaw('user_id', $chunk)
                        ->orWhereIntegerNotInRaw('assigned_user_id', $chunk)
                        ->delete(),
                    FlexmealLists::class => $className::whereIntegerNotInRaw('user_id', $chunk)->with('ingredients')->delete(),
                    ShoppingList::class  => $className::whereIntegerNotInRaw('user_id', $chunk)->delete(),
                    default              => $className::whereIntegerInRaw($key, $chunk)->delete()
                };
            }
            return [
                'deletedRecords' => $deletedRecords,
                'executionTime'  => $this->getTimeExecutionDiff()
            ];
        }

        // I do not want to make duplicated code parts some im forced to use this...
        $deletedRecords += match ($className) {
            ChargebeeSubscription::class => $className::whereIntegerNotInRaw('user_id', $idsToDelete)
                ->orWhereIntegerNotInRaw('assigned_user_id', $idsToDelete)
                ->delete(),
            FlexmealLists::class => $className::whereIntegerNotInRaw('user_id', $idsToDelete)->with('ingredients')->delete(),
            ShoppingList::class  => $className::whereIntegerNotInRaw('user_id', $idsToDelete)->delete(),
            default              => $className::whereIntegerInRaw($key, $idsToDelete)->delete()
        };

        return [
            'deletedRecords' => $deletedRecords,
            'executionTime'  => $this->getTimeExecutionDiff()
        ];
    }

    /**
     * Process data deletion and return counted values.
     * TODO: refactor as new tables are added.
     */
    private function processDelete(): array
    {
        return [
            'user_recipe_calculated' => $this->processDeletionViaQueryBuilder('user_recipe_calculated'),

            'user_recipe_calculated_preliminaries' => $this->processDeletionViaEloquent(UserRecipeCalculatedPreliminary::class),

            'user_recipe' => $this->processDeletionViaQueryBuilder(
                'user_recipe',
                'user_id',
                ['user_id'],
                'user_id'
            ),

            'recipes_to_users' => $this->processDeletionViaEloquent(
                UserRecipe::class,
                'user_id',
                ['user_id'],
                'user_id'
            ),

            //			'formulars' => $this->processDeletionViaEloquent(Formular::class),
            //TODO: remove deprecated
            //			'survey_answers' => $this->processDeletionViaEloquent(SurveyAnswer::class),

            'diary_datas' => $this->processDeletionViaEloquent(DiaryData::class),

            'course_users' => $this->processDeletionViaQueryBuilder('course_users'),

            'chargebee_subscriptions' => $this->processDeletionViaEloquent(
                ChargebeeSubscription::class,
                'user_id',
                ['user_id'],
                'user_id'
            ),

            'client_notes' => $this->processDeletionViaQueryBuilder('client_notes'),

            'custom_recipe_categories' => $this->processDeletionViaEloquent(CustomRecipeCategory::class),

            'custom_recipes' => $this->processDeletionViaEloquent(CustomRecipe::class),

            'favorites' => $this->processDeletionViaEloquent(Favorite::class),

            'flexmeal_lists|flexmeal_to_users' => $this->processDeletionViaEloquent(FlexmealLists::class),

            'personal_access_tokens' => $this->processDeletionViaQueryBuilder(
                'personal_access_tokens',
                'id',
                ['id', 'tokenable_id'],
                'tokenable_id',
                'tokenable_id'
            ),

            'posts' => $this->processDeletionViaEloquent(Post::class),

            'purchase_lists|purchase_lists_recipes|purchase_lists_ingredients' => $this->processDeletionViaEloquent(
                ShoppingList::class
            ),

            'ratings'                   => $this->processDeletionViaQueryBuilder('ratings'),
            'wallets_v2|transactions'   => $this->processDeletionViaQueryBuilder('wallets_v2'),
            'user_bulk_exclusions'      => $this->processDeletionViaQueryBuilder('user_bulk_exclusions'),
            'user_excluded_ingredients' => $this->processDeletionViaQueryBuilder('user_excluded_ingredients'),
            'user_excluded_recipes'     => $this->processDeletionViaQueryBuilder('user_excluded_recipes'),
            'user_subscription'         => $this->processDeletionViaEloquent(UserSubscription::class)
        ];
    }

    /**
     * Get time execution difference from initial timer.
     */
    private function getTimeExecutionDiff(): string
    {
        return Carbon::parse(microtime(true) - $this->startTimer)->toTimeString();
    }
}
