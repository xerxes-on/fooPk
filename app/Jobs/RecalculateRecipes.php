<?php

namespace App\Jobs;

use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Internal\Models\AdminStorage;

/**
 * Class RecalculateRecipes
 *
 * @package App\Jobs
 */
class RecalculateRecipes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CanGetProperty;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $recipeIds
     */
    public function __construct(private User $user, private $recipeIds)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        //        $curProgress = 0;
        //		try {
        //            event(new CalculationStatusUpdated($curProgress));
        //		} catch (\Exception $e) {
        //			logError($e);
        //		}

        // TODO:: add checking for replacement recipes intop user_recipe table
        // Preliminary calculations for all recipes in user's scope
        $jobStartHash = AdminStorage::generatePreliminaryJobHash($this->user->getKey());
        SyncUserExcludedIngredientsJob::dispatchSync($this->user);
        PreliminaryCalculation::dispatchSync($this->user, true, $jobStartHash);


        # recipe from user create
        foreach ($this->recipeIds as $index => $recipeId) {
            Calculation::_calcRecipe2user($this->user, [$recipeId]);

            //			$progress = ($index + 1) / count($this->recipeIds) * 100;
            //
            //			if ((round($progress) > $curProgress) && (round($progress) % 2 == 0)) {
            //				$curProgress = round($progress, 0);
            //
            //				//info(round($progress, 2) .'%');
            //
            //				try {
            //					//                    event(new CalculationStatusUpdated($curProgress));
            //				} catch (\Exception $e) {
            //					logError($e);
            //				}
            //			}
        }
    }
}
