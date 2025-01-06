<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\User;

use AdminForm;
use AdminFormElement;
use App\Admin\Http\Section\Display\DisplayTabbed;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Admin\Permission\RoleEnum;
use App\Enums\Admin\SectionPagesEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Events\RecipeProcessed;
use App\Jobs\DeleteUsers;
use App\Models\{Admin,
    Allergy,
    Diet,
    Ingestion,
    Questionnaire,
    QuestionnaireAnswer,
    QuestionnaireQuestion,
    Recipe,
    RecipeComplexity,
    RecipePrice,
    RecipeTag,
    SurveyQuestion,
    User};
use App\Services\Users\UserNutrientsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Builder};
use Meta;
use Modules\Course\Models\Course;
use Modules\Ingredient\Models\Ingredient;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Display\DisplayTabbed as NativeTabbed;
use SleepingOwl\Admin\Form\Element\MultiSelect;
use SleepingOwl\Admin\Form\FormElements;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section Users
 *
 * @property User $model
 * @property User $model_value
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Users extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fas fa-user';

    private bool $isConsultant = false;

    public function getTitle(): string
    {
        return trans('common.clients');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::USERS->value)
                ?->addPage((new Page(User::class)))?->setPriority(32);
        });
    }

    /**
     * @throws \Exception
     */
    public function onDisplay(): FormElements
    {
        $isConsultant          = auth()->user()->hasRole(RoleEnum::CONSULTANT->value);
        $hideRecipesRandomizer = !$isConsultant && auth()->user()->hasPermissionTo(PermissionEnum::ADD_RECIPES_TO_CLIENT->value);
        Meta::loadPackage(['dataTables', 'ladda']);
        Meta::addJs('client-display.js', mix('js/admin/client/display/index.js'));
        return AdminForm::elements(
            [
                AdminFormElement::view('admin::client.partials.global_display_data', [
                    'hideRecipesRandomizer' => $hideRecipesRandomizer,
                ]),
                AdminFormElement::custom()->setDisplay(
                    static fn() => view(
                        'admin::client.tableEntity',
                        [
                            'isConsultant'          => $isConsultant,
                            'hideRecipesRandomizer' => $hideRecipesRandomizer,
                            'aboChallenges'         => Course::get()->pluck('title', 'id')->toArray(),
                            'consultants'           => !$isConsultant ?
                                Admin::role(RoleEnum::CONSULTANT->value, RoleEnum::ADMIN_GUARD)
                                    ->pluck('name', 'id')
                                    ->toArray() :
                                []
                        ]
                    )
                ),
            ]
        );
    }

    public function setModelValue($item): void
    {
        $item->load(
            [
                'assignedChargebeeSubscriptions' => fn($query) => $query->with(['assignedClient', 'owner']),
                'chargebeeSubscriptions'         => fn($query) => $query->with(['assignedClient', 'owner']),
                'clientNotes'                    => fn($query) => $query->with(['author'])->orderBy('created_at', 'desc'),
                'activeSubscriptions',
            ]
        );
        $this->model_value  = $item;
        $this->isConsultant = auth()->user()->hasRole(RoleEnum::CONSULTANT->value);
    }

    /**
     * @throws \Exception
     */
    public function onEdit(int $id): FormInterface
    {
        // TODO: optimization required. Too much duplicated queries: ~100, and only ~48 unique
        $tabs = new DisplayTabbed();

        $this->addInfoTab($id, $tabs);
        $this->addBalanceTab($tabs);
        $this->addQuestionnaireTab($tabs);
        if (!$this->isConsultant) {
            $this->addFormularTab($tabs);
        }
        $this->addCalculationsTab($id, $tabs);
        $this->addRecipesTab($tabs);
        $this->addRecipesFromSubscriptionTab($tabs);
        $this->addSubscriptionsTab($tabs);
        if (!$this->isConsultant) {
            $this->addChargebeeSubscriptionsTab($tabs);
            $this->addCoursesTab($tabs);
        }

        return $tabs;
    }

    /**
     * @throws \Exception
     */
    private function addInfoTab(int $id, NativeTabbed $tabs): void
    {
        $serviceColumns = [
            AdminFormElement::view('admin::client.jobsStatus', ['client' => $this->model_value]),
            AdminFormElement::view('admin::client.partials.global_edit_data', [
                'clientID'                => $this->model_value->id,
                'subscription'            => $this->model_value->subscription, // todo:
                'canDeleteAllUserRecipes' => auth()->user()->can(PermissionEnum::DELETE_ALL_USER_RECIPES->value),
                'hideRecipesRandomizer'   => !auth()->user()->hasRole(RoleEnum::CONSULTANT->value) && auth()->user()->hasPermissionTo(PermissionEnum::ADD_RECIPES_TO_CLIENT->value),
            ]),
        ];

        $leftColumn = [
            AdminFormElement::checkbox('status', trans('common.enable_user'))->setView(view('admin::custom.switch')),
            AdminFormElement::checkbox('mark_tested', 'Set as test user')
                ->setExactValue($this->model_value->hasRole(RoleEnum::TEST_USER->value))
                ->setView(view('admin::custom.switch')),
            AdminFormElement::view('admin::custom.avatar_uploader', ['client' => $this->model_value]),
            AdminFormElement::text('first_name', trans('common.first_name'))->required(),
            AdminFormElement::text('last_name', trans('common.last_name')),
            AdminFormElement::text('email', trans('common.email'))->required(),
            AdminFormElement::select(
                'lang',
                trans('common.language'),
                array_map(static fn(string $value) => trans("admin.filters.language.$value"), config('translatable.locales'))
            ),
            AdminFormElement::select(
                'allow_marketing',
                trans('admin.filters.newsletter.title'),
                [0 => trans('admin.filters.defaults.missing'), 1 => trans('admin.filters.defaults.exist')]
            )
                ->setDisplay(
                    fn(User $recipe) => $recipe->allow_marketing ?
                        trans('admin.filters.defaults.exist') :
                        trans(
                            'admin.filters.defaults.missing'
                        )
                )
                ->setDefaultValue(0),
            AdminFormElement::password('new_password', trans('common.new_password'))
        ];

        // For dev purposes do not show "Recalculations job" view
        if (!config('foodpunk.check_user_recalculations')) {
            unset($serviceColumns[0]);
        }

        // Do not show "Set as test user" for consultants
        if ($this->isConsultant) {
            unset($leftColumn[1]);
        }

        $rightColumn = [
            AdminFormElement::view('admin::client.notes', ['client' => $this->model_value]),
            AdminFormElement::multiselect('bulkExclusions', trans('common.bulk exclusions'))
                ->setModelForOptions(Allergy::class)
                ->setLoadOptionsQueryPreparer(
                    fn(MultiSelect $element, Builder $query) => $query->whereHas(
                        'type',
                        function (Builder $builder) {
                            $builder->where('name', 'bulk exclusions');
                        }
                    )->listsTranslations('name')
                )
                ->setDisplay('name'),
            AdminFormElement::multiselect('excluded_ingredients', trans('common.excluded_ingredients'))
                ->setModelForOptions(Ingredient::class)
                ->setDisplay('name'),
            AdminFormElement::multiselect('excluded_recipes', trans('common.excluded_recipes'))
                ->setModelForOptions(Recipe::class)
                ->setLoadOptionsQueryPreparer(
                    fn(MultiSelect $element, Builder $query) => $query
                        ->listsTranslations('title')
                        ->select(['recipes.id', 'title', 'recipes.related_recipes'])
                        ->groupBy('title')
                )
                ->setDisplay(
                    fn(Recipe $recipe) => empty($recipe->related_recipes) ?
                        "$recipe->title #($recipe->id)" :
                        "$recipe->title #($recipe->id) with related (" . custom_implode($recipe->related_recipes) . ')'
                ),
        ];

        $liableAdmins = $this->model_value->liableAdmin()->get(['id', 'name'])->pluck('name')->toArray();
        if ([] !== $liableAdmins) {
            array_unshift(
                $rightColumn,
                '<h3>' .
                trans(
                    'admin.clients.fields.liable_admin',
                    ['name' => '<span class="badge badge-info">' . implode('</span>, <span class="badge badge-info">', $liableAdmins)]
                ) .
                '</span></h3>'
            );
        }

        $tabs->appendTab(
            AdminForm::panel()
                ->addStyle('switch.css', mix('css/admin/switch.css'))
                ->withPackage(['dataTables', 'ladda'])
                ->addScript('admin.js', mix('js/admin/admin.js'))
                ->addScript('client-edit.js', mix('js/admin/client/edit/index.js'))
                ->setAction(route('admin.client.store', ['id' => $id]))
                ->setItems(
                    AdminFormElement::columns()->addColumn(fn() => $serviceColumns, 12)
                        ->addColumn(fn() => $leftColumn, 6)
                        ->addColumn(fn() => $rightColumn, 6)
                ),
            trans('common.client_information')
        );
    }

    private function addBalanceTab(NativeTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view('admin::client.tab_balance', ['client' => $this->model_value]),
            trans('common.client_balance')
        );
    }

    private function addQuestionnaireTab(NativeTabbed $tabs): void
    {
        $questionnaireExist         = $this->model_value->isQuestionnaireExist();
        $admin                      = auth()->user();
        $latestQuestionnaire        = null;
        $latestQuestionnaireAnswers = null;
        $marketingData              = null;
        $marketingDataAnswers       = null;
        $questionnaireCount         = null;
        $clientQuestionnaire        = null;
        $baseQuestions              = QuestionnaireQuestion::baseOnly()->get(['id', 'slug']);

        // Fill data only if questionnaire exist
        if ($questionnaireExist) {
            $latestQuestionnaire        = $this->model_value->latestBaseQuestionnaire()->first();
            $latestQuestionnaireAnswers = $latestQuestionnaire
                ->answers
                ->mapWithKeys(function (QuestionnaireAnswer $item) use ($admin) {
                    $service = new $item->question->service(
                        $item,
                        $admin->lang,
                        questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB_EDITING,
                        user: $this->model_value
                    );
                    return [$item->question->slug => $service->getFormattedAnswer()];
                })
                ->toArray();
            // Some questions can be missing in questionnaire
            $latestQuestionnaireAnswers = array_replace(
                $baseQuestions->pluck('', 'slug')->toArray(),
                $latestQuestionnaireAnswers
            );

            // Build marketing questions
            $marketingData = $this->model_value->marketingQuestionnaire()->get();
            $marketingData = $marketingData
                ->filter(fn(Questionnaire $marketingData) => $marketingData->answers->isNotEmpty())
                ->first();
            $marketingDataAnswers = $marketingData
                ?->answers
                ?->mapWithKeys(function (QuestionnaireAnswer $item) use ($admin) {
                    $service = new $item->question->service(
                        $item,
                        $admin->lang,
                        questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB_EDITING,
                        user: $this->model_value
                    );
                    return [$item->question->slug => $service->getFormattedAnswer()];
                })
                ?->toArray();
            $questionnaireCount  = $this->model_value->questionnaire()->count();
            $clientQuestionnaire = $this->model_value->questionnaire()->get();
        }

        $tabFormular = AdminFormElement::view(
            'admin::client.tab_questionnaire',
            [
                'clientID'            => $this->model_value->id,
                'questionnaireExist'  => $questionnaireExist,
                'questionnaireCount'  => $questionnaireCount,
                'latestQuestionnaire' => $latestQuestionnaire,
                'latestAnswers'       => $latestQuestionnaireAnswers,
                'questionsMarketing'  => $marketingData,
                'answersMarketing'    => $marketingDataAnswers,
                'clientQuestionnaire' => $clientQuestionnaire,
                'baseQuestions'       => $baseQuestions,
            ]
        );

        $tabs->appendTab($tabFormular, trans('questionnaire.page_title'));
    }

    /**
     * @deprecated
     */
    private function addFormularTab(NativeTabbed $tabs): void
    {
        $lastAnswers   = [];
        $formularExist = $this->model_value->isFormularExist();
        if ($formularExist) {
            $countAndTime = [9, 10, 11];
            $disease      = [15, 16];

            foreach ($this->model_value->formular->answers->load('question') as $answer) {
                $objAnswers = json_decode((string)$answer->answer);
                $answerData = [];

                if (is_object($objAnswers)) {
                    foreach ($objAnswers as $name => $objAnswer) {
                        if (!is_null($objAnswer)) {
                            if (in_array($answer->survey_question_id, $countAndTime)) {
                                $answerData[] = trans('survey_questions.' . $name) . ': ' . $objAnswer;
                            } elseif ($name === 'no_matter') {
                                $answerData[] = trans('survey_questions.' . $answer->question->key_code . '_' . $name);
                            } elseif (in_array($answer->survey_question_id, $disease)) {
                                $answerData[] = $objAnswer;
                            } else {
                                $answerData[] = trans('survey_questions.' . $name);
                            }
                        }
                    }
                } elseif ($answer->question->id === 1) {
                    $answerData[] = parseDateString($answer->answer, 'd.m.Y');
                } elseif (
                    $answer->question->id === 4 &&
                    (!str_contains((string)$answer->answer, '.') || !str_contains((string)$answer->answer, '-'))
                ) {
                    try {
                        // TODO: old users are set by age not date birth. There are 90005 such users!
                        $dateOfBirth  = Carbon::parse($answer->answer);
                        $answerData[] = $dateOfBirth->format('d.m.Y') . ' (' . $dateOfBirth->age . ')';
                    } catch (\Exception $e) {
                        logError(
                            $e,
                            [
                                'client_id' => $this->model_value->id,
                                'answer'    => $answer->answer,
                                'note'      => 'probably one of users with date of birth set as age only'
                            ]
                        );
                        $answerData[] = $answer->answer; // Set in case it will be unable to parse data
                    }
                } elseif ('daily_routine' === $answer->question->key_code) {
                    $answerData[] = trans_fb("survey_questions.$answer->answer");
                } else {
                    $answerData[] = $answer->answer;
                }

                $lastAnswers[$answer->question->id] = [
                    'key_code' => $answer->question->key_code,
                    'answer'   => $answerData,
                ];
            }
        }

        $tabFormular = AdminFormElement::view(
            'admin::client.tab_formular',
            [
                'client'        => $this->model_value,
                'formularExist' => $formularExist,
                'formularCount' => $this->model_value->formulars()->count(),
                'questions'     => SurveyQuestion::all()->sortBy('id'),
                'formular'      => $lastAnswers
            ]
        );

        $tabs->appendTab($tabFormular, trans('common.formular.title'));
    }

    private function addCalculationsTab(int $id, DisplayTabbed $tabs): void
    {
        $dietdata = app(UserNutrientsService::class)->checkAndUpdateDietData($this->model_value);

        $dietdataTop = [
            'Daily Energy Kcal' => isset($dietdata['additional']['DailyEnergy']) ? round($dietdata['additional']['DailyEnergy']) : 0,
            'Calculated Kcal'   => $dietdata['additional']['calculated_Kcal'] ?? 0
        ];
        $tabCalculations = AdminFormElement::view(
            'admin::client.tab_calculations',
            [
                'is_consultant'        => $this->isConsultant,
                'client_diet_data_top' => $dietdataTop,
                'client_diet_data'     => $dietdata,
                'ingestions'           => Ingestion::where('active', '1')->get()->toArray(),
                'user_id'              => $id ?? null,
                'calc_auto'            => empty($id) ? false : $this->model_value->calc_auto
            ]
        );

        $tabs->appendTab($tabCalculations, trans('common.calculations'));
    }

    private function addRecipesTab(DisplayTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view(
                'admin::client.tab_recipes',
                [
                    'client'       => $this->model_value,
                    'ingestions'   => Ingestion::getAll(),
                    'complexities' => RecipeComplexity::getAll(),
                    'costs'        => RecipePrice::getAll(),
                    'diets'        => Diet::getAll(),
                    'tags'         => RecipeTag::withTranslation()->get(),
                ]
            ),
            trans('common.tabs_recipes')
        );
    }

    private function addRecipesFromSubscriptionTab(DisplayTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view('admin::client.tab_recipesFromSubscription', ['client' => $this->model_value]),
            trans('common.subscription_recipes')
        );
    }

    private function addSubscriptionsTab(DisplayTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view(
                'admin::client.tab_subscriptions',
                [
                    'client'        => $this->model_value,
                    'subscriptions' => $this->model_value?->subscriptions()->orderBy('active', 'desc')->get()
                ]
            ),
            trans('common.subscriptions')
        );
    }

    private function addChargebeeSubscriptionsTab(DisplayTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view(
                'admin::client.tab_chargebee_subscriptions',
                [
                    'client'        => $this->model_value,
                    'subscriptions' => $this->model_value
                        ?->assignedChargebeeSubscriptions
                        ->merge($this->model_value->chargebeeSubscriptions)
                        ->unique('id')
                ]
            ),
            trans('common.subscriptions_chargebee')
        );
    }

    private function addCoursesTab(DisplayTabbed $tabs): void
    {
        $tabs->appendTab(
            AdminFormElement::view(
                'admin::client.tab_challenges',
                [
                    'client'      => $this->model_value,
                    'courses'     => Course::all()->pluck('title', 'id')->toArray(),
                    'userCourses' => $this->model_value?->courses()->orderBy('pivot_ends_at', 'desc')->get()
                ]
            ),
            trans('course::common.courses')
        );
    }

    /**
     * @note Autocomplete is set to new-password to prevent browser from autofilling some fields.
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        $this->isConsultant = auth()->user()->hasRole(RoleEnum::CONSULTANT->value);
        $body               = [
            AdminFormElement::checkbox('status', trans('Enable User'))
                ->setExactValue('1')
                ->setView(view('admin::custom.switch')),
            AdminFormElement::checkbox('mark_tested', 'Set as test user')
                ->setExactValue('0')
                ->setView(view('admin::custom.switch')),
            AdminFormElement::checkbox('automatic_meal_generation', trans('admin.clients.fields.automatic_meal_generation'))
                ->setExactValue('1')
                ->setView(view('admin::custom.switch')),
            AdminFormElement::text('first_name', trans('common.first_name'))->required()->setHtmlAttribute('autocomplete', 'new-password'),
            AdminFormElement::text('last_name', trans('common.last_name'))->setHtmlAttribute('autocomplete', 'new-password'),
            AdminFormElement::text('email', trans('common.email'))->required()->setHtmlAttribute('autocomplete', 'new-password'),
            AdminFormElement::password('new_password', trans('common.new_password'))->setHtmlAttribute('autocomplete', 'new-password')
        ];

        // Do not show "Set as test user" for consultants
        if ($this->isConsultant) {
            unset($body[1], $body[2]);
        }

        return AdminForm::panel()
            ->addBody($body)
            ->addStyle('switch.css', mix('css/admin/switch.css'))
            ->addScript('admin.js', mix('js/admin/admin.js'))
            ->setAction(route('admin.client.create'));
    }

    public function onDelete(int $id): void
    {
        // Keep as is. Job will handle the deletions instead of SleepingOwl Vendor.
        DeleteUsers::dispatch([$id])->onQueue('high');
        RecipeProcessed::dispatch();
        $this->deleting(fn() => false);
    }
}
