<?php

namespace App\Services;

use Modules\Internal\Enums\JobProcessingEnum;
use Modules\Internal\Models\AdminStorage as AdminStorageModel;

/**
 * Service to handle Admin Storage demands.
 *
 * @package App\Services
 */
final class AdminStorage
{
    /**
     * Check if processed ingredient contain some data in cache or job DB.
     *
     * @param null|int $categoryId
     *
     * @return bool
     */
    public function checkIfIngredientCategoryJobsExist(?int $categoryId = null): bool
    {
        if (is_null($categoryId)) {
            return false;
        }

        $relatedJobs = AdminStorageModel::where('key', JobProcessingEnum::INGREDIENT_CATEGORY->value)->get();

        // collection empty -> return
        if ($relatedJobs->isEmpty()) {
            return false;
        }

        $filteredRelatedJobs = $relatedJobs->where('data.category_id', $categoryId)->first();

        // filter failed -> return
        if (is_null($filteredRelatedJobs)) {
            return false;
        }

        $jobCount = \DB::table('jobs')->where('id', $filteredRelatedJobs->data['related_job'])->get('id')->count();

        // if record exists but job is missing, clear value.
        if ($jobCount === 0) {
            $filteredRelatedJobs->delete();
        }

        return $jobCount > 0;
    }
}
