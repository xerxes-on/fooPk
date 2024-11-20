<?php

declare(strict_types=1);

namespace Modules\Ingredient\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Ingredient\Models\Ingredient;
use Modules\Internal\Enums\JobProcessingEnum;
use Modules\Internal\Models\AdminStorage;
use App\Http\Traits\Queue\HandleLastStartedJob;


/**
 * Job that recalculates users` forbidden ingredients.
 *
 * @package Modules\Ingredient\Jobs
 */
final class SyncUserExcludedIngredientsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HandleLastStartedJob;

    public function __construct(private readonly User $user)
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $this->relatedJobHash = AdminStorage::generateSyncUserExcludedIngredientsJobHash($this->user->getKey());
        $ingredients = Ingredient::getOnlyIds(); // Data is cached
        $allowedIngredients = $this->user->getGeneratedAllowedIngredientsList();

        if ($this->verifyOrFinishJob(JobProcessingEnum::USER_PROHIBITED_INGREDIENTS->value) === false) {
            return;
        }
        $this->user->saveProhibitedIngredients(array_diff($ingredients, $allowedIngredients));
    }
}
