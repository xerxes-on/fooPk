<?php

declare(strict_types=1);

namespace App\Services\Questionnaire;

use App\Enums\FormularCreationMethodsEnum;
use App\Exceptions\Questionnaire\AlreadyAvailableForEdit;
use App\Exceptions\Questionnaire\NotAvailableForEdit;
use App\Helpers\CacheKeys;
use App\Models\Questionnaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Service to check if a user can edit a questionnaire.
 *
 * @package Questionnaire
 */
final class QuestionnaireEditPossibilityCheckService
{
    private int $freeEditPeriod;
    private ?Questionnaire $questionnaire = null;
    private Carbon $now;

    public function __construct(private readonly User $user)
    {
        $this->freeEditPeriod = config('questionnaire.period_of_free_editing_in_days');
        $this->now            = Carbon::now();
    }

    public function checkPossibility(): bool
    {
        $data = Cache::get(CacheKeys::userCanEditQuestionnaire($this->user->id));
        if (!is_null($data)) {
            return $data;
        }

        try {
            $this->checkForSubscription();
            $this->checkQuestionnaireExistence();

            $this->setLatestQuestionnaire();

            $this->checkLatestQuestionnaireExistence();
            $this->checkQuestionnaireVisibility();
            $this->checkForImmediateChange();
            $this->checkForNewUser();
            $this->checkForRegularPeriod();
            // TODO:: review case when user has payed for edit, probably, it is checking incorrectly,
            // doesnt take into account creation method
            $this->checkForPaidPossibility();
        } catch (NotAvailableForEdit) {
            return false;
        } catch (AlreadyAvailableForEdit) {
            return true;
        }

        $this->storeInCache(false);
        return false;
    }

    /**
     * @note We must take into account only questionnaire created by user
     */
    private function setLatestQuestionnaire(): void
    {
        $this->questionnaire = $this->user->latestQuestionnaire()->where('creator_id')->first(['creation_method', 'created_at','is_editable']);
    }

    /**
     * User cannot edit questionnaire without active subscription
     *
     * @throws NotAvailableForEdit
     */
    private function checkForSubscription(): void
    {
        if ($this->user->activeSubscriptions()->count() === 0) {
            $this->cannotEdit();
        }
    }

    /**
     * Missing questionnaire -> Nothing to edit
     *
     * @throws NotAvailableForEdit
     */
    private function checkQuestionnaireExistence(): void
    {
        if ($this->user->isQuestionnaireExist()) {
            return;
        }
        $this->cannotEdit();
    }

    /**
     * Questionnaire is not created, missing or any error occurred
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkLatestQuestionnaireExistence(): void
    {
        if (is_null($this->questionnaire?->created_at)) {
            $this->storeInCache(true);
            $this->canEdit();
        }
    }

    /**
     * Admin or client forced visibility for questionnaire
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkQuestionnaireVisibility(): void
    {
        if ($this->questionnaire?->is_editable) {
            $this->storeInCache(true);
            $this->canEdit();
        }
    }

    /**
     * User can edit questionnaire if it is considered as an 'immediate' edition
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkForImmediateChange(): void
    {
        if (config('questionnaire.period_of_immediate_edit_in_minutes') >= $this->questionnaire->created_at->diffInMinutes($this->now)) {
            $this->storeInCache(true);
            $this->canEdit();
        }
    }

    /**
     * New users can edit questionnaire for free. Must be taken from the very first questionnaire.
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkForNewUser(): void
    {
        $oldestQuestionnaire = $this->user->questionnaire()->where('creator_id')->oldest()->get(['created_at'])->last();
        if ($this->freeEditPeriod >= $oldestQuestionnaire?->created_at?->diffInDays($this->now)) {
            $this->storeInCache(true);
            $this->canEdit();
        }
    }

    /**
     * User can edit questionnaire unlimited period after await period is over.
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkForRegularPeriod(): void
    {
        if ($this->freeEditPeriod <= $this->questionnaire->created_at->diffInDays($this->now)) {
            $this->storeInCache(true);
            $this->canEdit();
        }
    }

    /**
     * if User paid for editing we need to take previous questionnaire and check dates from there
     *
     * @throws AlreadyAvailableForEdit
     */
    private function checkForPaidPossibility(): void
    {
        if ($this->questionnaire->creation_method === FormularCreationMethodsEnum::PAID) {
            $previousFormular = $this
                ->user
                ->questionnaire()
                ->where('created_at', '<', $this?->questionnaire?->created_at)
                ->where('creator_id')
                ->first(['created_at']);
            if ($this->freeEditPeriod <= $previousFormular?->created_at?->diffInDays($this->now)) {
                $this->canEdit();
            }
        }
    }

    /**
     * @throws AlreadyAvailableForEdit
     */
    private function canEdit(): void
    {
        $this->storeInCache(true);
        throw new AlreadyAvailableForEdit();
    }

    /**
     * @throws NotAvailableForEdit
     */
    private function cannotEdit(): void
    {
        $this->storeInCache(false);
        throw new NotAvailableForEdit();
    }

    private function storeInCache(bool $value): void
    {
        Cache::put(CacheKeys::userCanEditQuestionnaire($this->user->id), $value, config('cache.lifetime_1m'));
    }
}
