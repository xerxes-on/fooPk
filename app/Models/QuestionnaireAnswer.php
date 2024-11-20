<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\QuestionnaireAnswerCast;
use App\Enums\DatabaseTableEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Questionnaire Answer model.
 *
 * @property int $id
 * @property int $questionnaire_id
 * @property int $questionnaire_question_id
 * @property array|string $answer
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\QuestionnaireQuestion|null $question
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereQuestionnaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereQuestionnaireQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionnaireAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class QuestionnaireAnswer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = DatabaseTableEnum::QUESTIONNAIRE_ANSWERS;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'questionnaire_id',
        'questionnaire_question_id',
        'answer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'answer' => QuestionnaireAnswerCast::class,
    ];

    /**
     * Relation to question.
     */
    public function question(): HasOne
    {
        return $this->hasOne(QuestionnaireQuestion::class, 'id', 'questionnaire_question_id');
    }
}
