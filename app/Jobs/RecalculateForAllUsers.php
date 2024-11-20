<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RecalculateForAllUsers
 *
 * @package App\Jobs
 */
class RecalculateForAllUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CanGetProperty;

    /**
     * RecalculateForAllUsers constructor.
     *
     * @param $userIds
     * @param $recipeId
     */
    public function __construct(private $userIds, private $recipeId)
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
        //			event(new CalculationStatusUpdated($curProgress));
        //		} catch (\Exception $e) {
        //			logError($e);
        //		}
        // todo: to improve it, user collection can be gather in advance with status filter and questionnaire already solved
        foreach ($this->userIds as $index => $userId) {
            # get user by Id
            $user = User::find($userId);

            # check user status or formular exist
            if ($user->status === false || !$user->isQuestionnaireExist()) {
                continue;
            }

            Calculation::_calcRecipe2user($user, [$this->recipeId]);

            //            $progress = ($index + 1) / count($this->userIds) * 100;

            //            if ((round($progress, 0) > $curProgress) && (round($progress) % 2 == 0)) {
            //                $curProgress = round($progress, 0);

            //info(round($progress, 2) .'%');

            //				try {
            //					event(new CalculationStatusUpdated($curProgress));
            //				} catch (\Exception $e) {
            //				}
            //            }
        }
    }
}
