<?php

namespace App\Http\Traits\Questionnaire\Model;

use App\Enums\Questionnaire\QuestionnaireQuestionStatusEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use Illuminate\Database\Eloquent\Builder;

trait QuestionnaireQuestionModelScope
{
    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereStatus(QuestionnaireQuestionStatusEnum::ACTIVE->value);
    }

    public function scopeForWeb(Builder $query): Builder
    {
        return $query->whereNotIn(
            'type',
            [QuestionnaireQuestionTypesEnum::INFO_PAGE->value, QuestionnaireQuestionTypesEnum::SALES_PAGE->value]
        )
            ->where('slug', '!=', 'email');
    }

    /**
     * Scope a query to get next active questions.
     */
    public function scopeNextActive(Builder $query, int $currentOrder): Builder
    {
        return $query->active()->where('order', '>', $currentOrder)->orderBy('order');
    }

    /**
     * Scope a query to get previous active questions.
     */
    public function scopePreviousActive(Builder $query, int $currentOrder): Builder
    {
        return $query->active()->where('order', '<', $currentOrder)->orderBy('order', 'desc');
    }

    /**
     * Scope a query to get only marketing questions.
     */
    public function scopeMarketingOnly(Builder $query): Builder
    {
        return $query
            ->whereNotIn(
                'type',
                [QuestionnaireQuestionTypesEnum::INFO_PAGE->value, QuestionnaireQuestionTypesEnum::SALES_PAGE->value]
            )
            ->where('is_editable', '=', 0);
    }

    /**
     * Scope a query to get only base questions.
     */
    public function scopeBaseOnly(Builder $query): Builder
    {
        return $query
            ->whereNotIn(
                'type',
                [QuestionnaireQuestionTypesEnum::INFO_PAGE->value, QuestionnaireQuestionTypesEnum::SALES_PAGE->value]
            )
            ->where('is_editable', '=', 1);
    }
}
