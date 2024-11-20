<?php

declare(strict_types=1);

namespace Modules\Ingredient\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Admin\Http\Section\SectionWithImage;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Models\{Seasons, Vitamin};
use Cookie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientTag as IngredientTagModel;
use Modules\Ingredient\Models\IngredientUnit;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Element\Select;
use SleepingOwl\Admin\Navigation\Page;

/**
 * Class Ingredients
 *
 * @property Ingredient $model
 * @property Ingredient|null $model_value
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class IngredientsAdminSection extends SectionWithImage implements Initializable
{
    public const ID = 'ingredients_section';

    /**
     * @var bool
     */
    protected $checkAccess = true;

    protected $icon = 'fas fa-seedling';

    public function getTitle(): string
    {
        return trans('common.ingredients');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation()
                ->setPriority(20)
                ->setId(self::ID)
                ->setAccessLogic(fn() => \Auth::user()?->hasPermissionTo(PermissionEnum::INGREDIENTS_MENU->value))
                ->setPages(function (Page $page) {
                    $page->addPage([
                        'title' => trans(
                            'common.new_ingredient',
                            locale: Cookie::get('translatable_lang', config('app.locale'))
                        ),
                        'url'      => 'admin/ingredients/create',
                        'priority' => 21,
                    ]);
                    $page->addPage((new Page(Ingredient::class))->setPriority(22));
                });
        });
    }

    /**
     * view grid Ingredients entity
     *
     * @throws \Exception
     */
    public function onDisplay(): DisplayInterface
    {
        $adminLang = \Auth::user()->lang;
        $table     = AdminDisplay::datatablesAsync()
            ->setHtmlAttribute('class', 'table-primary')
            ->setModelClass(Ingredient::class)
            ->with(
                [
                    'category.translations',
                    'tags.translations',
                    'unit.translations',
                    'seasons.translations',
                ]
            )
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth('5%')->setOrderable(),

                    AdminColumn::text(
                        'name',
                        trans('common.name')
                    )->setWidth('10%')->setOrderable(false),
                    AdminColumn::custom(
                        trans('common.category'),
                        static fn(Ingredient $model) => $model?->category?->name ?? trans('common.no_data')
                    )->setWidth('10%'),
                    AdminColumn::lists(static function (Ingredient $model) use ($adminLang) {
                        $data = [];
                        foreach ($model->category->diets as $diet) {
                            $data[] = $diet
                                ->translations
                                ->where('locale', $adminLang)
                                ->pluck('name');
                        }
                        return \Arr::flatten($data);
                    }, trans('common.diets'))->setWidth('15%'),
                    AdminColumn::lists(static function (Ingredient $model) use ($adminLang) {
                        $data = [];
                        foreach ($model->tags as $tag) {
                            $data[] = $tag
                                ->translations
                                ->where('locale', $adminLang)
                                ->pluck('title');
                        }
                        return \Arr::flatten($data);
                    }, trans('admin.tags'))->setWidth('15%'),
                    AdminColumn::text('proteins', trans('common.proteins'))->setWidth('5%'),
                    AdminColumn::text('fats', trans('common.fats'))->setWidth('5%'),
                    AdminColumn::text('carbohydrates', trans('common.carbohydrates'))->setWidth('5%'),
                    AdminColumn::text('calories', trans('common.calories'))->setWidth('5%'),
                    AdminColumn::custom(
                        trans('common.unit'),
                        static fn(Ingredient $model) => $model->unit->full_name
                    )->setWidth('10%'),
                    AdminColumn::lists(static function (Ingredient $model) use ($adminLang) {
                        $data = [];
                        foreach ($model->seasons as $season) {
                            $data[] = $season
                                ->translations
                                ->where('locale', $adminLang)
                                ->pluck('name');
                        }
                        return \Arr::flatten($data);
                    }, trans('common.season'), 'as')->setWidth('10%'),
                ]
            )
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'))
            ->addScript('ingredients.js', mix('js/admin/ingredients/ingredients.js'))
            ->paginate(20);

        $table->getExtension('links')?->setView(
            view(
                'admin::ingredient.search',
                [
                    'ingredient_categories' => IngredientCategory::get(),
                    'ingredient_tags'       => IngredientTagModel::get()
                ]
            )
        );

        return $table;
    }

    /**
     * create Ingredients entity
     *
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * Edit Ingredients entity
     *
     * @throws \Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        $adminLang = \Auth::user()->lang;
        $hints     = $this->model_value?->hint?->translations->where('locale', $adminLang)->first();
        $body      = [
            AdminFormElement::hidden('id'),
            AdminFormElement::text('name', trans('common.name'))
                ->setHtmlAttribute('required', 'required')
                ->required(),
            AdminFormElement::text('name_plural', trans('common.names'))
                ->setHelpText(trans('ingredient::admin.help.plural_name'))
                ->setHtmlAttribute('required', 'required')
                ->required(),
            AdminFormElement::view(
                'admin::ingredient.components.categorySelect',
                [
                    'ingredient_categories' => IngredientCategory::get(),
                    'ingredient'            => $id === null ? null : $this->model_value,
                ]
            ),
            '<h3>' . trans('common.nutritional_value') . '</h3>',
            AdminFormElement::columns()
                ->addColumn(fn() => [AdminFormElement::number('proteins', trans('common.EW'))
                    ->setMin(0)
                    ->setStep(0.01)
                    ->setHtmlAttribute('required', 'required')
                    ->required(),])
                ->addColumn(fn() => [AdminFormElement::number('fats', trans('common.F'))
                    ->setMin(0)
                    ->setStep(0.01)
                    ->setHtmlAttribute('required', 'required')
                    ->required(),]),
            AdminFormElement::columns()
                ->addColumn(fn() => [AdminFormElement::number('carbohydrates', trans('common.KH'))
                    ->setMin(0)
                    ->setStep(0.01)
                    ->setHtmlAttribute('required', 'required')
                    ->required(),])
                ->addColumn(fn() => [AdminFormElement::number('calories', trans('common.KCAL'))
                    ->setMin(0)
                    ->setStep(0.01)
                    ->setHtmlAttribute('required', 'required')
                    ->required(),]),
            '<h3>' . trans('common.units') . '</h3>',
            AdminFormElement::columns()
                ->addColumn(fn() => [AdminFormElement::select('unit_id', trans('ingredient::admin.primary_unit_title'), IngredientUnit::class)
                    ->setLoadOptionsQueryPreparer(function (Select $element, Builder $query) {
                        return $query->isPrimary();
                    })
                    ->setDisplay('full_name')
                    ->setHtmlAttribute('required', 'required')
                    ->required()])
                ->addColumn(fn() => [AdminFormElement::select('alternative_unit_id', trans('ingredient::admin.secondary_unit_title'), IngredientUnit::class)
                    ->setLoadOptionsQueryPreparer(function (Select $element, Builder $query) {
                        return $query->IsSecondary();
                    })
                    ->setHtmlAttribute('required', 'required')]),
            AdminFormElement::number('unit_amount', trans('ingredient::common.unit_amount'))
                ->setHelpText(trans('ingredient::admin.help.unit_amount'))
                ->setDefaultValue(0)
                ->setMin(0),
            AdminFormElement::multiselect('seasons', trans('common.season'), Seasons::class)
                ->setDisplay('name'),
            AdminFormElement::multiselectajax(
                'tags',
                trans('common.tags'),
            )
                ->setModelForOptions(IngredientTagModel::class)
                ->setSearchUrl(route('admin.search_ingredient_tag.select2'))
                ->setMinSymbols(1)
                ->setDisplay(fn($model) => "[$model->id] $model->title")
                ->setSelect2Options(
                    ['placeholder' => trans('common.select_tag'), 'ajax--delay' => 400]
                )
                ->setHelpText(trans('admin_help_text.ingredient_tags_help_text')),
            '<h3>' . trans('admin.ingredient_hint.title') . '</h3>',
            AdminFormElement::textarea('hint.content', trans('common.content'))->setExactValue($hints?->content),
            AdminFormElement::text('hint.link_text', trans('PushNotification::admin.notification_title', ['lang' => '']))->setExactValue($hints?->link_text),
            AdminFormElement::text('hint.link_url', trans('PushNotification::admin.notification_link_url'))->setExactValue($hints?->link_url)
        ];

        $vitamins = is_null($id) ? Vitamin::withTranslation()->get() : $this->model_value->vitamins;
        $vitamins->loadMissing('translations');
        $body[] = '<h3>' . trans('common.vitamins') . '</h3>';
        foreach ($vitamins as $vitamin) {
            $value  = $vitamin->pivot->value ?? 0;
            $body[] = AdminFormElement::number(
                "vitamins[$vitamin->id]",
                $vitamin->translations->where('locale', $adminLang)->first()->name
            )
                ->setDefaultValue($value)
                ->setMin(0)
                ->setStep(0.01);
        }

        $existInRecipes = $this->model_value?->present_in_recipes;
        if (!empty($existInRecipes)) {
            Session::flash(
                'info_message',
                trans(
                    'admin.messages.record_blocked_by_dependency',
                    ['recipes' => custom_implode($existInRecipes, prepend: '<br>')]
                )
            );
        }
        return AdminForm::panel()
            ->addBody($body)
            ->addScript('ingredients.js', mix('js/admin/ingredients.js'))
            ->setAction(route('admin.ingredients.store'));
    }

    public function isDeletable(Model $model): bool
    {
        return !$model->exist_in_recipes;
    }

    public function onDelete(): void
    {
        // Do not allow to delete an ingredient if it is used in recipes
        if ($this->model->exist_in_recipes) {
            $this->deleting(fn() => false);
        }

        $this->model->delete();
    }
}
