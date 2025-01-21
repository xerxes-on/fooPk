<?php

declare(strict_types=1);

namespace App\Http\Traits\Questionnaire\Model;

use App\Enums\Questionnaire\Options\MealPerDayQuestionOptionsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Helpers\CacheKeys;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Services\Questionnaire\QuestionnaireEditPossibilityCheckService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;

trait HasQuestionnaire
{
    /**
     * Relation for users questionnaire
     */
    public function questionnaire(): HasMany
    {
        return $this->hasMany(Questionnaire::class)->orderBy('questionnaires.id', 'desc');
    }

    /**
     * Relation for latest users questionnaire
     */
    public function latestQuestionnaireRelation(): HasOne
    {
        return $this->hasOne(Questionnaire::class)->orderBy('questionnaires.id', 'desc');
    }

    /**
     * Check whether user has filled questionnaire.
     */
    public function isQuestionnaireExist(): bool
    {
        $data = Cache::get(CacheKeys::userQuestionnaireExists($this->id));
        if (!is_null($data)) {
            return $data;
        }
        $data = $this->getQuestionnaireExistsStatus();
        Cache::put(CacheKeys::userQuestionnaireExists($this->id), $data, config('cache.lifetime_short'));
        return $data;
    }

    /**
     * Get info whether user has filled questionnaire.
     */
    public function getQuestionnaireExistsStatus(): bool
    {
        return $this
                ->whereHas('latestQuestionnaireRelation')
                ->where('users.id', $this->id)
                ->count() > 0;
    }

    /**
     * Check if latest user questionnaire is approved.
     */
    public function getQuestionnaireApprovedAttribute(): bool
    {
        return (bool)$this->latestQuestionnaire()->first('is_approved')?->is_approved;
    }

    /**
     * Check if user can edit questionnaire.
     */
    public function canEditQuestionnaire(): bool
    {
        return app(QuestionnaireEditPossibilityCheckService::class, ['user' => $this])->checkPossibility();
    }

    /**
     * Scope to get latest questionnaire.
     */
    public function scopeLatestQuestionnaire(): HasMany
    {
        return $this->questionnaire()->latest('id')->limit(1);
    }

    /**
     * Scope to get latest questionnaire with only base questions and answers.
     */
    public function scopeLatestBaseQuestionnaire(): HasMany
    {
        return $this
            ->latestQuestionnaire()
            ->with('answers', function (HasMany $relation) {
                $relation
                    ->whereIn(
                        'questionnaire_answers.questionnaire_question_id',
                        function (QueryBuilder $query) {
                            $query->select('id')
                                ->from((new QuestionnaireQuestion())->getTable())
                                ->whereNotIn(
                                    'type',
                                    [
                                        QuestionnaireQuestionTypesEnum::INFO_PAGE->value,
                                        QuestionnaireQuestionTypesEnum::SALES_PAGE->value
                                    ]
                                )
                                ->where('is_editable', '=', 1);
                        }
                    )
                    ->distinct()
                    ->with('question');
            });
    }


    /**
     * Scope to get latest questionnaire with fully required questions and answers.
     * made as hotfix for partly-created questionnaries WEB-685
     * TODO::@NickMost restore all uses after 2024 June to scopeLatestBaseQuestionnaire
     */
    public function scopeLatestBaseQuestionnaireWithAllRequiredAnswers(): HasMany
    {
        // TODO:: @NickMost review which questions must be
        $questionsIdRequiredAndEditable = QuestionnaireQuestion::whereIsRequired(true)->whereIsEditable(true)->pluck('id');

        // getting questionnaire answers where all required answers are
        return $this->questionnaire()->whereIn(
            'questionnaires.id',
            function (QueryBuilder $query) use ($questionsIdRequiredAndEditable) {
                $query->select('questionnaire_id')
                    ->from((new QuestionnaireAnswer())->getTable())
                    ->whereIn('questionnaire_id', $this->latestQuestionnaireRelation()->pluck('questionnaires.id'))
                    ->whereIn('questionnaire_question_id', $questionsIdRequiredAndEditable)
                    ->groupBy('questionnaire_id')
                    ->havingRaw('COUNT(DISTINCT questionnaire_question_id) = ?', [count($questionsIdRequiredAndEditable)])
                    ->pluck('questionnaire_id');
            }
        )->with('answers', function (HasMany $relation) {
            $relation
                ->whereIn(
                    'questionnaire_answers.questionnaire_question_id',
                    function (QueryBuilder $query) {
                        $query->select('id')
                            ->from((new QuestionnaireQuestion())->getTable())
                            ->whereNotIn(
                                'type',
                                [
                                    QuestionnaireQuestionTypesEnum::INFO_PAGE->value,
                                    QuestionnaireQuestionTypesEnum::SALES_PAGE->value
                                ]
                            )
                            ->where('is_editable', '=', 1);
                    }
                )
                ->distinct()
                ->with('question');
        });
    }

    /**
     * Scope to get questionnaire with only marketing questions and answers.
     */
    public function scopeMarketingQuestionnaire(): HasMany
    {
        return $this
            ->questionnaire()
            ->with('answers', function (HasMany $relation) {
                $relation
                    ->whereIn(
                        'questionnaire_answers.questionnaire_question_id',
                        function (QueryBuilder $query) {
                            $query->select('id')
                                ->from((new QuestionnaireQuestion())->getTable())
                                ->whereNotIn(
                                    'type',
                                    [
                                        QuestionnaireQuestionTypesEnum::INFO_PAGE->value,
                                        QuestionnaireQuestionTypesEnum::SALES_PAGE->value
                                    ]
                                )
                                ->where('is_editable', '=', 0);
                        }
                    )
                    ->distinct()
                    ->with('question');
            });
    }

    /**
     * Get excluded ingredients from latest questionnaire.
     */
    public function getLatestQuestionnaireExcludedIngredientsAttribute(): ?array
    {
        // TODO: maybe cache this at minimum time
        return $this
            ->latestQuestionnaire()
            ->with('answers', function (HasMany $relation) {
                $relation
                    ->where(
                        'questionnaire_answers.questionnaire_question_id',
                        function (QueryBuilder $query) {
                            $query->select(['id'])
                                ->from((new QuestionnaireQuestion())->getTable())
                                ->where('slug', '=', QuestionnaireQuestionSlugsEnum::EXCLUDE_INGREDIENTS);
                        }
                    )
                    ->distinct()
                    ->with('question');
            })
            ->first(['id'])
            ?->answers
            ->pluck('answer')
            ->flatten()
            ->toArray();
    }

    /**
     * Get excluded ingredients from latest questionnaire.
     */
    public function getLatestQuestionnaireGoalAttribute(): ?string
    {
        return $this
            ->latestQuestionnaire()
            ->with('answers', function (HasMany $relation) {
                $relation
                    ->where(
                        'questionnaire_answers.questionnaire_question_id',
                        function (QueryBuilder $query) {
                            $query->select(['id'])
                                ->from((new QuestionnaireQuestion())->getTable())
                                ->where('slug', '=', QuestionnaireQuestionSlugsEnum::MAIN_GOAL);
                        }
                    )
                    ->distinct()
                    ->with('question');
            })
            ->first(['id'])
            ?->answers
            ->pluck('answer')
            ->first();
    }

    /**
     * Get excluded ingredients from latest questionnaire.
     */
    public function scopeLatestQuestionnaireSpecificAnswer(Builder $builder, int $questionId): HasMany
    {
        return $this
            ->latestQuestionnaire()
            ->with('answers', function (HasMany $relation) use ($questionId) {
                $relation
                    ->where(
                        'questionnaire_answers.questionnaire_question_id',
                        $questionId
                    )
                    ->distinct()
                    ->with('question');
            });
    }

    /**
     * Users latest questionnaire answers.
     */
    public function getLatestQuestionnaireAnswersAttribute(): ?array
    {
        // TODO: maybe cache this at minimum time
        return $this
            ->latestBaseQuestionnaire()
            ->first()
            ?->answers
            ->mapWithKeys(
                function (QuestionnaireAnswer $item) {
                    // TODO: probably need to pass source type in initialisation
                    $service = new $item->question->service($item, $this->lang, user: $this);
                    return [$item->question->slug => $service->getAnswer()];
                }
            )
            ->toArray();
    }

    /**
     * Users latest questionnaire full answers.
     */
    public function getLatestQuestionnaireFullAnswersAttribute(): ?array
    {
        $latest = $this->relationLoaded('latestQuestionnaireRelation')
            ? $this->getRelation('latestQuestionnaireRelation')
            : $this->latestQuestionnaireRelation()->first();

        if (!$latest) {
            return null;
        }
        if (!$latest->relationLoaded('answers')) {
            $latest->load('answers.question');
        }
        return $latest->answers
            ->mapWithKeys(function (QuestionnaireAnswer $item) {
                $service = new $item->question->service($item, $this->lang, user: $this);
                return [$item->question->slug => $service->getAnswer()];
            })
            ->toArray();
    }

    /**
     * User previous approved questionnaire answers.
     */
    public function getPreviousApprovedQuestionnaireAnswersAttribute(): ?array
    {
        $result        = [];
        $questionnaire = $this->latestQuestionnaire()->first();
        if ($questionnaire) {
            $previousQuestionnaire = $this->questionnaire()
                ->where('created_at', '<', $questionnaire->created_at)
                ->where('is_approved', 1)
                ->with('answers.question')
                ->limit(1)
                ->first();

            if ($previousQuestionnaire) {
                return $previousQuestionnaire
                    ?->answers
                    ->mapWithKeys(
                        function (QuestionnaireAnswer $item) {
                            // TODO: probably need to pass source type in initialisation
                            $service = new $item->question->service($item, $this->lang, user: $this);
                            return [$item->question->slug => $service->getAnswer()];
                        }
                    )
                    ->toArray();
            }
        }
        return $result;
    }

    /**
     * Check if user has changed meal per day
     */
    public function getHasChangedMealPerDayAttribute(): bool
    {
        $result                        = false;
        $latestQuestionnaireAnswers    = $this->latestQuestionnaireAnswers;
        $previousApprovedQuestionnaire = $this->PreviousApprovedQuestionnaireAnswers;
        if (
            !empty($previousApprovedQuestionnaire) &&
            !empty($latestQuestionnaireAnswers) &&
            isset($latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY]) &&
            (
                (
                    isset($previousApprovedQuestionnaire[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])
                    &&
                    $previousApprovedQuestionnaire[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY] != $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY]
                )
                ||
                !isset($previousApprovedQuestionnaire[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])
            )

        ) {
            // case when not exists previous answer
            if (isset($this->dietdata['ingestion']) && !isset($previousApprovedQuestionnaire[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY])) {
                $hasBreakfast = !empty($this->dietdata['ingestion']['breakfast']['percents']);
                $hasLunch     = !empty($this->dietdata['ingestion']['lunch']['percents']);
                $hasDinner    = !empty($this->dietdata['ingestion']['dinner']['percents']);

                $previousMealAmountValue = false;
                if ($hasBreakfast && $hasLunch && $hasDinner) {
                    $previousMealAmountValue = MealPerDayQuestionOptionsEnum::STANDARD->value;
                } elseif ($hasBreakfast && $hasLunch) {
                    $previousMealAmountValue = MealPerDayQuestionOptionsEnum::BREAKFAST_LUNCH->value;
                } elseif ($hasBreakfast && $hasDinner) {
                    $previousMealAmountValue = MealPerDayQuestionOptionsEnum::BREAKFAST_DINNER->value;
                } elseif ($hasLunch && $hasDinner) {
                    $previousMealAmountValue = MealPerDayQuestionOptionsEnum::LUNCH_DINNER->value;
                }

                $result = $previousMealAmountValue != $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MEALS_PER_DAY];
            } else {
                $result = true;
            }
        }
        return $result;
    }



    /**
     * Get diets from latest questionnaire.
     */
    public function getLatestQuestionnaireDietsAttribute(): ?array
    {
        // TODO: maybe cache this at minimum time
        return $this
            ->latestQuestionnaire()
            ->with('answers', function (HasMany $relation) {
                $relation
                    ->where(
                        'questionnaire_answers.questionnaire_question_id',
                        function (QueryBuilder $query) {
                            $query->select(['id'])
                                ->from((new QuestionnaireQuestion())->getTable())
                                ->where('slug', '=', QuestionnaireQuestionSlugsEnum::DIETS);
                        }
                    )
                    ->distinct()
                    ->with('question');
            })
            ->first(['id'])
            ?->answers
            ->pluck('answer')
            ->flatten()
            ->toArray();
    }
}
