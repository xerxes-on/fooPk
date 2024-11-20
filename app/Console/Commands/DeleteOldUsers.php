<?php

namespace App\Console\Commands;

use App\Jobs\DeleteUsers;
use App\Mail\ExportUpdateNotifier;
use App\Models\{Allergy, Formular, Recipe, SurveyAnswer, SurveyQuestion, User};
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\{Builder, Collection, Relations\BelongsToMany, Relations\HasMany};
use Illuminate\Support\Facades\{Bus, Mail, Storage};
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Ingredient\Models\Ingredient;
use Throwable;

/**
 * Delete old users data by special conditions and export specific data to Excel.
 * @deprecated This command is deprecated and should be removed or refactored according to actual user data!
 * @package App\Console\Commands
 */
final class DeleteOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean {limit=10000} {--dry-run : Simulate the command without deleting any data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old users data by special conditions and export specific data to Excel';

    /**
     * Limit of user per query.
     * This is used to prevent memory overflow.
     */
    private int $queryLimit = 10000;

    /**
     * Simulate the command without deleting any data.
     */
    private bool $dryRun = false;

    /**
     * Collection of questions data.
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyQuestion>|null
     */
    private ?Collection $questions = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->setupDefaults();
        if ($this->dryRun) {
            $this->info('Dry run activated. No data will be deleted.');
        }

        // 1. find users that meet the criteria.
        $this->info('Searching users...');
        $group1 = $this->getUserGroup(1);
        $group2 = $this->getUserGroup(2);
        $group3 = $this->getUserGroup(3);

        // 2. Export the data and save to a file.
        $this->info('Generating export file...');
        $filePath = '/exports/users_from_' . Str::slug(now()->toDateTimeString()) . '.json';
        Storage::disk('local')->put(
            $filePath,
            collect(
                [
                    'condition1' => $this->transformUserGroup($group1),
                    'condition2' => $this->transformUserGroup($group2),
                    'condition3' => $this->transformUserGroup($group3)
                ]
            )->toJson()
        );

        // 3. gather users IDs are only they are required now.
        $group1 = $group1->pluck('id')->toArray();
        $group2 = $group2->pluck('id')->toArray();
        $group3 = $group3->pluck('id')->toArray();

        // 4. send file to responsive admins via email
        $this->info("Export file at `storage/app$filePath` generated. Sending email to admins");
        Mail::send(
            new ExportUpdateNotifier($filePath, ['condition1' => $group1, 'condition2' => $group2, 'condition3' => $group3])
        );

        // 5. Delete all user belongings (what exactly should be deleted)
        if (!$this->dryRun) {
            $this->info('Setting delete users jobs...');
            DeleteUsers::dispatch($group1)->delay(now()->addDays(14));
            Bus::chain([new DeleteUsers($group2), new DeleteUsers($group3)])->catch(fn(Throwable $e) => logError($e))->dispatch();
            $this->info('Delete user commands dispatched.');
        }

        return Command::SUCCESS;
    }

    /**
     * Retrieve user collection matching the group condition described by business requirements.
     *
     * @param int $groupCondition
     *
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\User>
     */
    private function getUserGroup(int $groupCondition): Collection
    {
        $match = match ($groupCondition) {
            /**
             * Combination 1:
             *
             * no formular / formular not approved
             * no active subscription
             * account disabled
             *
             * @note deletion should be delayed for 14 days from running time.
             */
            1 => User::where('status', 0)
                ->doesntHave('formulars')
                ->orWhereHas(
                    'formulars',
                    function (Builder $builder) {
                        $builder->latest('id')->where('approved', 0);
                    }
                )
                ->doesntHave('subscriptions')
                ->with(
                    [
                        'excludedIngredients' => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'excludedRecipes'     => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'bulkExclusions'      => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'formulars'           => fn(HasMany $q) => $q->withOnly('answers')
                    ]
                )
                ->leftJoin('client_notes', 'users.id', '=', 'client_notes.client_id')
                ->select(
                    [
                        'users.id',
                        'users.first_name',
                        'users.last_name',
                        'users.email',
                        'users.status',
                        'users.dietdata',
                        'users.created_at',
                        'users.updated_at',
                        'client_notes.text',
                    ]
                )
                ->limit($this->queryLimit)
                ->get(),
            /**
             * Combination 2:
             *
             * no formular
             * no active chargebee subscription
             * account enabled
             * registration date > 1 year ago
             */
            2 => User::where(
                [
                    ['users.status', 1],
                    ['users.created_at', '<=', now()->subYear()]
                ]
            )
                ->doesntHave('formulars')
                ->doesntHave('chargebeeSubscriptions')
                ->with(
                    [
                        'excludedIngredients' => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'excludedRecipes'     => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'bulkExclusions'      => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'formulars'           => fn(HasMany $q) => $q->withOnly('answers')
                    ]
                )
                ->leftJoin('client_notes', 'users.id', '=', 'client_notes.client_id')
                ->select(
                    [
                        'users.id',
                        'users.first_name',
                        'users.last_name',
                        'users.email',
                        'users.status',
                        'users.dietdata',
                        'users.created_at',
                        'users.updated_at',
                        'client_notes.text',
                    ]
                )
                ->limit($this->queryLimit)
                ->get(),
            /**
             * Combination 3:
             *
             * formular approved
             * no active subscription
             * no active chargebee subscription
             * registration date > 2 years ago
             * registration date after 30.06.2019
             */
            3 => User::where(
                [
                    ['users.created_at', '<=', now()->subYears(2)],
                    ['users.created_at', '>=', Carbon::parse('30.06.2019')->toDateTimeString()]
                ]
            )
                ->whereHas(
                    'formulars',
                    function (Builder $builder) {
                        $builder->latest('id')->where('approved', 1);
                    }
                )
                ->doesntHave('subscriptions')
                ->doesntHave('chargebeeSubscriptions')
                ->with(
                    [
                        'excludedIngredients' => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'excludedRecipes'     => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'bulkExclusions'      => fn(BelongsToMany $q) => $q->withOnly('translations'),
                        'formulars'           => fn(HasMany $q) => $q->withOnly('answers')
                    ]
                )
                ->leftJoin('client_notes', 'users.id', '=', 'client_notes.client_id')
                ->select(
                    [
                        'users.id',
                        'users.first_name',
                        'users.last_name',
                        'users.email',
                        'users.status',
                        'users.dietdata',
                        'users.created_at',
                        'users.updated_at',
                        'client_notes.text',
                    ]
                )
                ->limit($this->queryLimit)
                ->get(),
            default => null
        };

        if (is_null($match)) {
            throw new InvalidArgumentException('Wrong argument passed for ' . __METHOD__);
        }

        return $match;
    }

    /**
     * Transform user group collection to match the required output.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $group
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    private function transformUserGroup(Collection $group): Collection
    {
        return $group->transform(
            function (User $item) {
                $attributes                         = $item->getAttributes();
                $attributes['excluded_ingredients'] = $item
                    ->excludedIngredients
                    ->map(
                        fn(Ingredient $ingredient) => ['id' => $ingredient->id, 'name' => $ingredient->translate('de')->name]
                    )
                    ->toArray();

                $attributes['excluded_recipes'] = $item
                    ->excludedRecipes
                    ->map(
                        fn(Recipe $recipe) => ['id' => $recipe->id, 'name' => $recipe->translate('de')->title]
                    )
                    ->toArray();

                $attributes['bulk_exclusions'] = $item
                    ->bulkExclusions
                    ->map(
                        fn(Allergy $allergy) => ['id' => $allergy->id, 'name' => $allergy->translate('de')->name]
                    )
                    ->toArray();

                $attributes['formulars_history'] = $item
                    ->formulars
                    ->map(
                        fn(Formular $formular) => $formular->answers->map(
                            function (SurveyAnswer $answer) {
                                /**
                                 * @note flag JSON_THROW_ON_ERROR trows syntax error here.
                                 * This is because not all answers are json.
                                 */
                                $objAnswers   = json_decode($answer->answer);
                                $countAndTime = [9, 10, 11];
                                $disease      = [15, 16];
                                $answerData   = $answer?->answer ?? 'no answer';
                                if (is_object($objAnswers)) :
                                    foreach ($objAnswers as $name => $objAnswer):
                                        if (!is_null($objAnswer)):
                                            $answerData = trans('survey_questions.' . $name, [], 'de');

                                            if (in_array($answer->survey_question_id, $countAndTime)):
                                                $answerData = trans('survey_questions.' . $name, [], 'de') . ': ' . $objAnswer;
                                            elseif ($name == 'no_matter') :
                                                $answerData = trans(
                                                    'survey_questions.' . $answer->question->key_code . '_' . $name,
                                                    [],
                                                    'de'
                                                );
                                            elseif (in_array($answer->survey_question_id, $disease)):
                                                $answerData = $objAnswer ?? 'no answer';
                                            endif;
                                        endif;
                                    endforeach;
                                elseif ($answer->survey_question_id == 1):
                                    $answerData = parseDateString($answer->answer, 'd.m.Y');
                                elseif (
                                    $answer->survey_question_id == 4 &&
                                    (str_contains($answer->answer, '.') || str_contains($answer->answer, '-'))
                                ):
                                    $answerData = parseDateString($answer->answer, 'd.m.Y') . ' ('
                                        . Carbon::parse($answer->answer)->age . ')';
                                endif;

                                return [
                                    'question' => trans(
                                        'survey_questions.' .
                                        $this->questions->where('id', $answer->survey_question_id)->first()->key_code,
                                        [],
                                        'de'
                                    ),
                                    'answer' => strip_tags($answerData)
                                ];
                            }
                        )
                    )
                    ->toArray();

                return $attributes;
            }
        );
    }

    /**
     * Setup default values for the command.
     */
    private function setupDefaults(): void
    {
        $this->queryLimit = (int)$this->argument('limit');
        $this->dryRun     = (bool)$this->option('dry-run');
        $this->questions  = SurveyQuestion::orderBy('id')->get(['id', 'key_code', 'active']);
    }
}
