<?php

namespace App\Http\Traits\Scope;

use App\Enums\FormularCreationMethodsEnum;
use App\Models\Formular;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

trait UserModelDeprecations
{
    /**
     * *new logic*
     *
     * relation get all formulars
     * @deprecated
     */
    public function formulars(): HasMany
    {
        return $this->hasMany(Formular::class)->orderBy('id', 'desc');
    }

    /**
     * Whether a user filled a formular.
     * @deprecated
     */
    public function isFormularExist(): bool
    {
        // TODO: probably should be cached in order to improve performance
        $formular = $this->formular; // Introduced to decrease query duplication by 20!!
        return !empty($formular) && $formular->answers->count() > 0;
    }


    /**
     * check can Edit Formular
     * @deprecated
     */
    public function canEditFormular(): bool
    {
        // Missing formular -> Nothing to edit | TODO: subscription should be cached or done via shorter sql request
        if (!$this->isFormularExist() && is_null($this->subscription)) {
            return false;
        }

        $formular = $this->formular;

        // formular is not created or any error occurred
        if (is_null($formular?->created_at)) {
            return true;
        }

        // if admin or client forced visibility for formular
        if ($formular?->forced_visibility) {
            return true;
        }

        $now = Carbon::now();

        // User can edit formular if it is considered as an 'immediate' edition
        if (config('formular.period_of_immediate_edit_in_minutes') >= $formular->created_at->diffInMinutes($now)) {
            return true;
        }

        $freeEditPeriod = config('formular.period_of_free_editing_in_days');

        // New users can edit formular for free. Must be taken from the very first formular.
        if ($freeEditPeriod >= $this->formulars()->oldest()->get()->last()?->created_at?->diffInDays($now)) {
            return true;
        }

        // User can edit formular unlimited period after await period is over.
        if ($freeEditPeriod <= $formular->created_at->diffInDays($now)) {
            return true;
        }

        // if User paid for editing we need to take previous formular and check dates from there
        if ($formular->creation_method === FormularCreationMethodsEnum::PAID) {
            $previousFormular = $this->formulars()->where('created_at', '<', $formular->created_at)->limit(1)->first();
            if ($freeEditPeriod <= $previousFormular?->created_at?->diffInDays($now)) {
                return true;
            }
        }

        return false;
    }

    /**
     * *new logic*
     *
     * get last formular and answers
     * @return mixed
     * @deprecated
     */
    public function getFormularAttribute()
    {
        $formular = Cache::get($this->getFormularCacheKey());

        if (!empty($formular)) {
            return $formular;
        }

        // TODO: add withCount here and refactor places where it's invoked. This will decrease number of additional request
        $formular = $this->formulars()->latest('id')->with('answers')->first();
        Cache::put($this->getFormularCacheKey(), $formular);

        return $formular;
    }

    /**
     * relation get active challenge
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     * @deprecated
     * TODO: must be replace for subscription
     *
     */
    public function getChallengeAttribute()
    {
        return $this->subscriptions()->where('active', true)->first();
    }

    /**
     * get Formular Cache key
     */
    protected function getFormularCacheKey(): string
    {
        return 'user-' . $this->id . '-formular-answers';
    }

    /**
     * Clear Formular Cache
     */
    public function forgetFormularCache(): bool
    {
        return Cache::forget($this->getFormularCacheKey());
    }
}
