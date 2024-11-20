<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Recipe;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Admin\Http\Controllers\Recipe\RecipeAdminController;
use App\Admin\Http\Section\SectionWithImage;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Models\{Diet, Ingestion, Inventory, Recipe as RecipeModel, RecipeComplexity, RecipePrice, RecipeTag};
use Cookie;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\{Badge, Page};

/**
 * Class Recipe
 *
 * @property RecipeModel $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Recipe extends SectionWithImage implements Initializable
{
    public const ID = 'recipes_section';

    protected $checkAccess = true;

    protected $controllerClass = RecipeAdminController::class;

    protected $icon = 'fas fa-cloud-meatball';

    public function getTitle(): string
    {
        return trans('common.recipes');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation()
                ->setPriority(10)
                ->setId(self::ID)
                ->setAccessLogic(static fn() => \Auth::user()->hasPermissionTo(PermissionEnum::RECIPES_MENU->value))
                ->setPages(function (Page $page) {
                    $locale = Cookie::get('translatable_lang', config('app.locale'));
                    $page->addPage(
                        [
                            'title'       => trans('common.new_recipe', locale: $locale),
                            'priority'    => 11,
                            'url'         => route('admin.model.create', ['adminModel' => 'recipes']),
                            'accessLogic' => static fn() => \Auth::user()->hasPermissionTo(PermissionEnum::CREATE_RECIPE->value)
                        ]
                    );
                    $page->addPage(
                        (new Page(RecipeModel::class))
                            ->setPriority(12)
                    );
                    $page->addPage([
                        'title'       => trans('common.import_recipes', locale: $locale),
                        'priority'    => 19,
                        'badge'       => new Badge('Dev'),
                        'url'         => 'admin/recipes/import',
                        'accessLogic' => static fn() => \Auth::user()->hasPermissionTo(PermissionEnum::IMPORT_RECIPE->value)
                    ]);
                });
        });
    }

    /**
     * @throws Exception by mix() method
     */
    public function onDisplay(): DisplayInterface
    {
        $display = AdminDisplay::datatablesAsync()
            ->setHtmlAttribute('class', 'table-primary hide-new-entry')
            ->setDatatableAttributes(['dom' => 'rt<"row"<"col-12"i><"col-12"p>>'])
            ->setModelClass(RecipeModel::class)
            ->with(
                [
                    'translations',
                    'complexity.translations',
                    'diets.translations',
                    'tags.translations',
                ]
            );

        $this->addColumns($display);
        $this->addAssets($display);
        $this->addView($display);

        return $display->paginate(20);
    }

    /**
     * @throws BindingResolutionException
     */
    private function addView(DisplayInterface $display): void
    {
        $display->getExtension('links')->setView(
            view(
                'admin::recipe.search',
                [
                    'ingestions'   => Ingestion::getAll(),
                    'complexities' => RecipeComplexity::getAll(),
                    'costs'        => RecipePrice::getAll(),
                    'diets'        => Diet::getAll(),
                    'ingredients'  => Ingredient::getAll(),
                    'tags'         => RecipeTag::withTranslation()->get(['id']),
                ]
            )
        );
    }

    private function addColumns(DisplayInterface $display): void
    {
        $display->setColumns(
            [
                AdminColumn::text('id', '#')->setWidth('5%'),
                AdminColumn::custom(
                    trans('common.image'),
                    fn(RecipeModel $model) => $this->getImageWithUrl(
                        $model->image->url(),
                        $model->image->url('thumb')
                    )
                )->setWidth('8%'),
                AdminColumn::text('title', trans('common.title'))
                    ->setWidth('20%')
                    ->setOrderable(false),
                AdminColumn::custom(
                    trans('common.cooking_time'),
                    static fn(RecipeModel $model) => is_null($model?->cooking_time) && is_null($model?->unit_of_time) ?
                        '—' :
                        $model->cooking_time . ' ' . trans('common.' . $model->unit_of_time)
                )->setWidth('8%'),
                AdminColumn::custom(
                    trans('common.complexity'),
                    static fn(RecipeModel $model) => is_null($model?->complexity_id) ?
                        '—' :
                        $model->complexity->title
                )->setWidth('8%'),
                AdminColumn::lists('ingestions.title', trans('common.meal'))->setWidth('10%'),
                AdminColumn::lists('diets.name', trans('common.diets'))->setWidth('10%'),
                AdminColumn::custom(
                    trans('common.status'),
                    static fn(RecipeModel $model) => trans('common.'.$model->status->lowerName())
                )->setWidth('3%'),
                AdminColumn::custom(
                    trans('admin.recipes.translations_done'),
                    static fn(RecipeModel $model) => $model->translations_done ? trans('common.yes') : trans('common.no')
                )->setWidth('3%'),
                AdminColumn::lists('seasons.name', trans('common.season'))->setWidth('13%'),
                AdminColumn::lists('tags.title', trans('admin.recipe_tag.title'))->setWidth('15%')
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function addAssets(DisplayInterface $display): void
    {
        $display->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'))
            ->addScript('recipes.js', mix('js/admin/recipes/recipes.js'));
    }

    /**
     * create Recipe entity
     *
     * @throws Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit Recipe entity
     * @throws Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        $model = $id === null ? null : $this->model_value?->loadMissing(['ingredients']);

        // Instantiate form to be able to retrieve and set up form control buttons
        $form = AdminForm::form()
            ->addStyle('loader.css', mix('css/loader.css'))
            ->addStyle('customImage.css', mix('css/admin/customImage.css'))
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'))
            ->addScript('admin.js', mix('js/admin/admin.js'))
            ->setAction(route('admin.recipes.store'));

        // Setup form control buttons to be able to set duplicates to the top of the form
        $buttons = $form->getButtons()->setModel($this->getModelValue() ?? new RecipeModel())->setModelConfiguration($this);

        // Setup main form elements
        $form->setElements([
            view('admin::recipe.btnGroup', ['id' => $id, 'buttons' => $buttons]),
            view('admin::recipe.draftBlock', ['recipe' => $model]),
            AdminFormElement::hidden('id')->setValue($id),
            AdminFormElement::image('image', trans('common.image'))
                ->setView(view('admin::custom.image')),
            AdminFormElement::textarea('title', trans('common.title'))
                ->setRows(3)
                ->required(),
            AdminFormElement::columns()
                ->addColumn(
                    static fn() => [
                        AdminFormElement::number('cooking_time', trans('common.cooking_time'))
                            ->setMin(0)
                            ->setStep(0.1)
                    ]
                )
                ->addColumn(
                    static fn() => [
                        AdminFormElement::select(
                            'unit_of_time',
                            trans('common.unit_of_time'),
                            [
                                'minutes' => trans('common.minutes'),
                                'hours'   => trans('common.hours')
                            ]
                        )
                    ]
                ),

            AdminFormElement::multiselectajax('related', trans('common.related_recipes_title'))
                ->setModelForOptions(RecipeModel::class)
                ->setSearchUrl(route('admin.search-recipes.select2', ['excludedId' => $id ?? 0]))
                ->setMinSymbols(1)
                ->setDisplay(static fn($model) => "[$model->id] $model->title")
                ->setSelect2Options(
                    [
                        'placeholder' => trans('common.select_new_recipe'),
                        'ajax--delay' => 400
                    ]
                )
                ->setHelpText(trans('admin_help_text.related_recipes_help_text')),

            AdminFormElement::columns()
                ->addColumn(
                    static fn() => [
                        AdminFormElement::multiselect('ingestions', trans('common.meal'), Ingestion::class)
                            ->setDisplay('title')
                    ]
                )
                ->addColumn(
                    static fn() => [
                        AdminFormElement::multiselect('inventory', trans('common.inventory'), Inventory::class)
                            ->setDisplay('title')
                    ]
                ),
            AdminFormElement::columns()
                ->addColumn(
                    static fn() => [
                        AdminFormElement::select(
                            'price_id',
                            trans('common.recipe_price'),
                            RecipePrice::class
                        )
                            ->setDisplay('title')
                    ]
                )
                ->addColumn(
                    static fn() => [
                        AdminFormElement::select('complexity_id', trans('common.complexity'), RecipeComplexity::class)
                            ->setDisplay('title')
                    ]
                ),

            AdminFormElement::view(
                'admin::recipes',
                [
                    'ingredients_categories' => IngredientCategory::withOnly(['translations'])
                        ->ofAllCategories()
                        ->get(),
                    'ingredients' => Ingredient::count('id'),// Ensure ingredients exist
                    'recipe'      => $model,
                ]
            )
        ]);

        return $form;
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
