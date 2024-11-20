<?php

declare(strict_types=1);

namespace App\Services\Questionnaire;

use App\Enums\FormularCreationMethodsEnum;
use App\Exceptions\PublicException;
use App\Exceptions\Questionnaire\AlreadyAvailableForEdit;
use App\Models\User;
use Carbon\Carbon;
use Throwable;

/**
 * Service to handle client's questionnaire.
 *
 * @package App\Services\Questionnaire
 */
final class ClientQuestionnaire
{
    /**
     * Handling opportunity to buying formular editing.
     *
     * @throws \App\Exceptions\PublicException
     * @throws \App\Exceptions\Questionnaire\AlreadyAvailableForEdit
     */
    public function processBuyEditing(User $user): void
    {
        // check if feature is enabled for fronted
        if (false === (bool)config('formular.ability_forced_formular_editing_by_client_enabled')) {
            throw new PublicException(trans('api.formular_edit_prohibited'));
        }

        // check if formular can be edited for free. if so - just redirect to editing page
        if ($user->canEditQuestionnaire()) {
            throw new AlreadyAvailableForEdit();
        }

        $editingPrice = (int)config('formular.formular_editing_price_foodpoints');
        // check user has enough foodpoints on account
        if (!$user->canWithdraw($editingPrice)) {
            throw new PublicException(trans('questionnaire.info.insufficient_fund'));
        }

        // proceed with editing - withdraw foodpoints from balance
        try {
            $user->withdraw($editingPrice, ['description' => 'Purchase of Formular edit']);
        } catch (Throwable $e) {
            logError($e);
            throw new PublicException(trans('questionnaire.info.withdraw_error'));
        }

        //  Make formular able to edit by user
        if (false === $user->latestQuestionnaire()->update(['is_editable' => 1])) {
            throw new PublicException(trans('questionnaire.info.not_saved_error'));
        }
    }

    /**
     * Get amount of days left until free editing.
     */
    public function getFreeEditPeriod(User $user): int
    {
        $questionnaire  = $user->latestQuestionnaire()->first(['creation_method', 'created_at']);
        $now            = Carbon::now();
        $freeEditPeriod = config('questionnaire.period_of_free_editing_in_days');
        $daysLeft       = $freeEditPeriod - $questionnaire?->created_at?->diffInDays($now) % $freeEditPeriod;

        if ($questionnaire->creation_method === FormularCreationMethodsEnum::PAID) {
            $previousFormular = $user
                ->questionnaire()
                ->where('created_at', '<', $questionnaire->created_at)
                ->limit(1)
                ->first('created_at');
            $daysLeft = $freeEditPeriod - $previousFormular?->created_at?->diffInDays($now) % $freeEditPeriod;
        }

        return $daysLeft;
    }
}
