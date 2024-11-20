<?php

declare(strict_types=1);

namespace Modules\Ingredient\Services;

use App\Enums\AllergyTypeEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Models\Allergy;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;

/**
 * Service for generating user's allowed and prohibited ingredients based on his medical conditions and preferences.
 * @package Modules\Ingredient\Services
 */
final class UserIngredientGeneratorService
{
    private array $excludedCategories    = [];
    private array $excludedIngredientIds = [];
    private array $allowedDiets          = [];

    private User $user;
    private ?array $latestQuestionnaireAnswers = null;

    public function getUserAllowedIngredientIds(User $user): array
    {
        $this->gatherBaseData($user);
        return Ingredient::withOnly('category')
            ->whereIntegerNotInRaw('id', $this->excludedIngredientIds)
            ->pluck('id')
            ->toArray();
    }

    public function getUserProhibitedIngredientIds(User $user): array
    {
        $this->gatherBaseData($user);
        return $this->excludedIngredientIds;
    }

    private function gatherBaseData(User $user): void
    {
        $this->user                       = $user;
        $this->latestQuestionnaireAnswers = $user->latestQuestionnaireAnswers;
        if ($this->latestQuestionnaireAnswers) {
            $this->processAllergies();
            $this->processDiseases();
        }
        $this->processBulkExclusions();
        $this->processDiets(); // categories could only BE ALLOWED
        $this->gatherExcludedIngredientsByDietCategory();
        $this->processCategories(); // categories could only BE EXCLUDED

        // final ingredients merging and preparations
        $this->gatherExcludedIngredients();
    }

    private function processAllergies(): void
    {
        $this->processAllergiesDiseasesBulkExclusions(
            $this->gatherMedicalConditions(
                $this->latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::ALLERGIES] ?? [],
                AllergyTypeEnum::ALLERGY
            )
        );
    }

    private function processDiseases(): void
    {
        $this->processAllergiesDiseasesBulkExclusions(
            $this->gatherMedicalConditions(
                $this->latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::DISEASES] ?? [],
                AllergyTypeEnum::DISEASE
            )
        );
    }

    private function processBulkExclusions(): void
    {
        $this->processAllergiesDiseasesBulkExclusions(
            $this->gatherMedicalConditions(
                $this->user->bulkExclusions()->pluck('slug')->toArray(),
                AllergyTypeEnum::BULK_EXCLUSIONS
            )
        );
    }

    private function processDiets(): void
    {
        $userAllowedDiets = $this->latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::DIETS] ?? [];
        if (empty($userAllowedDiets)) {
            return;
        }
        $dietsId = [];
        // TODO:: review possible diets and relation to answers in questionnaire, ketogenic example
        $currentDietsConfig = config('diets');
        $diets              = $userAllowedDiets;
        foreach ($diets as $diet) {
            if (empty($currentDietsConfig[$diet])) {
                continue;
            }
            if (is_array($currentDietsConfig[$diet])) {
                foreach ($currentDietsConfig[$diet] as $dietId) {
                    $dietsId[] = (int)$dietId;
                }
                continue;
            }
            $dietsId[] = (int)$currentDietsConfig[$diet];
        }

        $this->allowedDiets = array_merge($this->allowedDiets, $dietsId);
        $this->allowedDiets = array_unique($this->allowedDiets);
    }

    private function gatherExcludedIngredientsByDietCategory(): void
    {
        if (empty($this->allowedDiets)) {
            return;
        }

        // ingredient_categories_to_diets
        $allowedCategoriesId = [];
        foreach ($this->allowedDiets as $dietId) {
            $categoriesIdsByDiet = \DB::table('ingredient_categories_to_diets')
                ->where('diet_id', $dietId)
                ->pluck('ingredient_category_id')
                ->toArray();
            if (empty($allowedCategoriesId)) {
                $allowedCategoriesId = $categoriesIdsByDiet;
                continue;
            }
            $allowedCategoriesId = array_intersect($allowedCategoriesId, $categoriesIdsByDiet);
        }

        // recursive generating all child categories from the excluded list
        foreach ($allowedCategoriesId as $categoryId) {
            $childrenIds = $this->getIngredientCategoryChildren($categoryId);

            if (empty($childrenIds)) {
                continue;
            }
            $childrenIds = array_map(static fn($item) => $item->id, $childrenIds);
            // We must repopulate $allowedCategoriesId to check all children categories (array_merge will be longer)
            array_walk($childrenIds, static function (int $item) use (&$allowedCategoriesId) {
                $allowedCategoriesId[] = $item;
            });
        }

        $allowedCategoriesId = array_unique($allowedCategoriesId, SORT_NUMERIC);

        // if empty it means that all categories are restricted
        if (!empty($allowedCategoriesId)){
            // getting firstly allowed ingredients by categories, because could be ingredients in not allowed categories and in allowed at the same time
            $excludedIngredientsByDiets = Ingredient::whereNotIn(
                'id',
                function ($query) use($allowedCategoriesId) {
                    $query->select('id')
                        ->from((new Ingredient())->getTable())
                        ->whereIn(
                            'category_id',
                            $allowedCategoriesId
                        );
                }
            )
            ->pluck('id')->toArray();

            $this->excludedIngredientIds = array_merge($this->excludedIngredientIds, $excludedIngredientsByDiets);
        }
        else{
            // TODO:: @NickMost review that, case when not exists possible categories
//            $this->excludedIngredientIds = Ingredient::get()->pluck('id')->toArray();
        }

    }

    private function processCategories(): void
    {
        if (empty($this->excludedCategories)) {
            return;
        }
        // TODO: excluded categories should be processed recursively
        // recursive generating all child categories from the excluded list

        foreach ($this->excludedCategories as $categoryID) {
            $childrenIds = $this->getIngredientCategoryChildren($categoryID);

            if (empty($childrenIds)) {
                continue;
            }
            $childrenIds = array_map(static fn($item) => $item->id, $childrenIds);
            // We must repopulate $excludedCategories to check all children categories (array_merge will be longer)
            array_walk($childrenIds, function (int $item) {
                $this->excludedCategories[] = $item;
            });
        }

        $this->excludedCategories = array_unique($this->excludedCategories, SORT_NUMERIC);
        sort($this->excludedCategories, SORT_NUMERIC);

        $excludedIngredientsByCategoriesGeneral = Ingredient::whereIntegerInRaw('category_id', $this->excludedCategories)
            ->setEagerLoads([])
            ->pluck('id')
            ->toArray();
        if (empty($excludedIngredientsByCategoriesGeneral)) {
            return;
        }

        $this->excludedIngredientIds = array_merge($this->excludedIngredientIds, $excludedIngredientsByCategoriesGeneral);
    }

    private function gatherExcludedIngredients(): void
    {
        $excludedIngredientIds = isset($this->latestQuestionnaireAnswers['exclude_ingredients']) ?
            array_map(static fn($item) => $item['key'], $this->latestQuestionnaireAnswers['exclude_ingredients']) :
            $this->user->excludedIngredients()->pluck('ingredients.id')->toArray();
        $this->excludedIngredientIds = array_merge($this->excludedIngredientIds, $excludedIngredientIds);
        $this->excludedIngredientIds = array_unique($this->excludedIngredientIds, SORT_NUMERIC);
        sort($this->excludedIngredientIds);
    }

    /**
     * @param Collection<int,Allergy> $conditions
     */
    private function processAllergiesDiseasesBulkExclusions(Collection $conditions): void
    {
        foreach ($conditions as $condition) {
            foreach ($condition->ingredientCategories as $category) {
                if (!empty($category->id)) {
                    $this->excludedCategories[] = (int)$category->id;
                }
            }
            foreach ($condition->ingredients as $ingredient) {
                if (!empty($ingredient->id)) {
                    $this->excludedIngredientIds[] = (int)$ingredient->id;
                }
            }
            foreach ($condition->allowedDiets as $diet) {
                if (!empty($diet->id)) {
                    $this->allowedDiets[] = (int)$diet->id;
                }
            }
        }
    }

    /**
     * @return Collection<int,Allergy>
     */
    private function gatherMedicalConditions(array $conditions, AllergyTypeEnum $type): Collection
    {
        return Allergy::whereIn('slug', $conditions)
            ->where('type_id', $type->value)
            ->with([
                'ingredientCategories' => fn(BelongsToMany $relation) => $relation->setEagerLoads([])->select(['id', 'parent_id']),
                'ingredients'          => fn(BelongsToMany $relation) => $relation->setEagerLoads([])->select(['ingredients.id']),
                'allowedDiets'         => fn(BelongsToMany $relation) => $relation->setEagerLoads([])->select(['diets.id']),
            ])
            ->get(['id']);
    }

    private function getIngredientCategoryChildren(int $categoryID): array
    {
        $cache = \Cache::get('ingredient_categories_children_' . $categoryID);

        if ($cache !== null) {
            return $cache;
        }

        $query = "SELECT `id`
                FROM (SELECT `id`,`parent_id` FROM `ingredient_categories`
                         ORDER BY `parent_id`, `id`) `ingredient_categories`,
                        (SELECT @pv := '$categoryID') initialisation
                WHERE find_in_set(parent_id, @pv) > 0 AND @pv := concat(@pv, ',', id)";
        $cache = \DB::select($query);
        \Cache::put('ingredient_categories_children_' . $categoryID, $cache, config('cache.lifetime_1m'));

        return $cache;
    }
}
