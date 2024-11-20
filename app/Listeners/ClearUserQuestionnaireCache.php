<?php

namespace App\Listeners;

use App\Helpers\CacheKeys;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Cache;

final class ClearUserQuestionnaireCache extends EventBase
{
    /**
     * Handle the event.
     *
     * @param object|null $event
     * @return void
     */
    public function handle($event = null): void
    {
        if (empty($this->userId)) {
            return;
        }

        Cache::forget(CacheKeys::userQuestionnaireExists($this->userId));
        Cache::forget(CacheKeys::userCanEditQuestionnaire($this->userId));
        Cache::forget(CacheKeys::userExcludedRecipesIds($this->userId));
        QuestionnaireUserSession::flushData($this->userId);
    }
}
