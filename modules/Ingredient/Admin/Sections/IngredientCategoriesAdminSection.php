<?php

declare(strict_types=1);

namespace Modules\Ingredient\Admin\Sections;

use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Jobs\RecalculateRecipeDiets;
use App\Models\Diet;
use App\Services\AdminStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Modules\Ingredient\Enums\IngredientCategoryEnum;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Model\ModelConfiguration;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class IngredientCategories
 *
 * @property IngredientCategory $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class IngredientCategoriesAdminSection extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.ingredients_category');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(IngredientsAdminSection::ID)
                ?->addPage((new Page(IngredientCategory::class))->setPriority(23));
        });
    }

    /**
     * view grid IngredientCategories entity
     *
     * @throws \Exception
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::tree()
            ->setValue('name')
            ->setReorderable(false)
            ->addStyle('ingredientCategories.css', mix('css/admin/ingredientCategories.css'));
    }

    /**
     * create IngredientCategories entity
     *
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit IngredientCategories entity
     *
     * @throws \Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        $category = $this->model->find($id);

        $data = [
            'category'              => $category,
            'categories'            => $this->model->get(),
            'diets'                 => Diet::getAll(),
            'calculationsJobExists' => app(AdminStorage::class)->checkIfIngredientCategoryJobsExist($id),
            'mainCategory'          => $this->model->majorCategories()->get(),
            'midCategories'         => $category ?
                $this->model->midCategories($category->id, $category->tree_information['main_category'])->get() :
                null,
            'categoryDiets' => $category?->diets->pluck('id')->toArray()
        ];

        if ($data['calculationsJobExists']) {
            Session::flash('info_message', trans('admin.messages.record_blocked_by_job'));
        }

        if ($category) {
            $this->preventDeletingRootCategory($category);
        }

        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('name', trans('common.name'))->required(),
                    view(
                        'admin::ingredient.categories.ingredientCategory',
                        $data
                    ),
                ]
            )
            ->addScript('ingredientCategories.js', mix('js/admin/ingredients/ingredient-categories.js'))
            ->setAction(route('admin.ingredientCategories.store'));
    }

    public function isDeletable(Model $model): bool
    {
        return config('foodpunk.disable_ingredients_category_deletion') !== true && parent::isDeletable($model);
    }

    /**
     * delete IngredientCategories entity
     */
    public function onDelete(): void
    {
        // Prevent deleting if background tasks exists.
        if ((new AdminStorage())->checkIfIngredientCategoryJobsExist($this->model->id)) {
            Session::flash('info_message', trans('admin.messages.record_blocked_by_job'));

            $this->deleting(fn() => false);
        }

        // Prevent deleting root category
        if (is_null($this->model?->parent_id) || is_null($this->model?->tree_information['mid_category'])) {
            Session::flash('error_message', trans('common.forbid_to_remove_record'));

            $this->deleting(fn() => false);
        }

        $ids        = [];
        $identifier = null;

        if (is_null($this->model?->parent_id)) {
            $identifier = 'main_category'; // delete main category
        } elseif (is_null($this->model?->tree_information['mid_category'])) {
            $identifier = 'mid_category'; // delete mid category
        }

        if (!is_null($identifier)) {
            $categories = IngredientCategory::get();
            foreach ($categories as $category) {
                if ($category->tree_information[$identifier] == $this->model->id) {
                    $ids[] = $category->id;
                }
            }

            IngredientCategory::ofIds($ids)->delete();
        }

        $ids[] = $this->model->id;

        // remove category ingredients by setting them to first category (spices), thus not leaving null field
        Ingredient::whereIn('category_id', $ids)->update(['category_id' => IngredientCategoryEnum::SPICES->value]);

        RecalculateRecipeDiets::dispatch();

        $this->model->delete();
    }

    private function preventDeletingRootCategory(IngredientCategory $category): void
    {
        AdminSection::registerModel(
            IngredientCategory::class,
            static function (ModelConfiguration $model) use ($category) {
                if (is_null($category?->parent_id) || is_null($category?->tree_information['mid_category'])) {
                    $model->disableDeleting();
                }
            }
        );
    }
}
