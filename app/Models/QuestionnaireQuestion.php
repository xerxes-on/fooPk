<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Questionnaire\QuestionnaireQuestionStatusEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionTypesEnum;
use App\Http\Traits\Questionnaire\Model\QuestionnaireQuestionModelScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Questionnaire Question Model
 *
 * @property int $id
 * @property string $slug
 * @property QuestionnaireQuestionTypesEnum $type Type of question. See QuestionnaireQuestionTypesEnum.php
 * @property int $order
 * @property QuestionnaireQuestionStatusEnum $status Question status. See QuestionnaireQuestionStatusEnum.php
 * @property string $service Fully qualified responsible service class name
 * @property bool $is_required Is it required to answer this question
 * @property bool $is_editable 0 - should appear once, 1 - can be edited multiple times
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Builder|QuestionnaireQuestion active()
 * @method static Builder|QuestionnaireQuestion baseOnly()
 * @method static Builder|QuestionnaireQuestion forWeb()
 * @method static Builder|QuestionnaireQuestion marketingOnly()
 * @method static Builder|QuestionnaireQuestion newModelQuery()
 * @method static Builder|QuestionnaireQuestion newQuery()
 * @method static Builder|QuestionnaireQuestion nextActive(int $currentOrder)
 * @method static Builder|QuestionnaireQuestion previousActive(int $currentOrder)
 * @method static Builder|QuestionnaireQuestion query()
 * @method static Builder|QuestionnaireQuestion whereCreatedAt($value)
 * @method static Builder|QuestionnaireQuestion whereId($value)
 * @method static Builder|QuestionnaireQuestion whereIsEditable($value)
 * @method static Builder|QuestionnaireQuestion whereIsRequired($value)
 * @method static Builder|QuestionnaireQuestion whereOrder($value)
 * @method static Builder|QuestionnaireQuestion whereService($value)
 * @method static Builder|QuestionnaireQuestion whereSlug($value)
 * @method static Builder|QuestionnaireQuestion whereStatus($value)
 * @method static Builder|QuestionnaireQuestion whereType($value)
 * @method static Builder|QuestionnaireQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class QuestionnaireQuestion extends Model
{
    use QuestionnaireQuestionModelScope;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'questionnaire_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'slug',
        'type',
        'order',
        'status',
        'service',
        'is_required',
        'is_editable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'type'        => QuestionnaireQuestionTypesEnum::class,
        'status'      => QuestionnaireQuestionStatusEnum::class,
        'is_required' => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Bootstrap the model and its traits.
     * Here we try to set the status to draft if the service is empty.
     * @note It does not work for mass update.
     */
    public static function boot(): void
    {
        parent::boot();
        self::creating(static function (QuestionnaireQuestion $question) {
            if (empty($question->service)) {
                $question->status = QuestionnaireQuestionStatusEnum::DRAFT;
            }
        });
        self::updating(static function (QuestionnaireQuestion $question) {
            if (empty($question->service)) {
                $question->status = QuestionnaireQuestionStatusEnum::DRAFT;
            }
        });
        self::saving(static function (QuestionnaireQuestion $question) {
            if (empty($question->service)) {
                $question->status = QuestionnaireQuestionStatusEnum::DRAFT;
            }
        });
    }
}
