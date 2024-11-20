<?php

namespace App\Services\Users;

use App\Helpers\Calculation;
use App\Models\User;

/**
 * Service class for managing user's nutrients.
 *
 * @package App\Services\Users
 */
final class UserNutrientsService
{
    public function checkAndUpdateDietData(User $user)
    {
        $dietData = $user->dietdata;
        // TODO:: @NickMost to think about calc_auto? And method return typehint
        if ($user->isQuestionnaireExist() && $user->questionnaireApproved === true && (empty($dietData) || $user->calc_auto)) {
            $dietData = Calculation::calcUserNutrients($user->id);
            if ($dietData) {
                $user->dietdata = $dietData;
                $user->save();
            }
        }
        return $dietData;
    }
}
