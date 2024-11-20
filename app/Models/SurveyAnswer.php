<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SurveyAnswer
 *
 * @property-read int $id
 * @property-read \App\Models\User $user
 * @property int $formular_id
 * @property-read \App\Models\SurveyQuestion $survey_question_id
 * @property \App\Models\Admin $challenge_id
 * @property-read \App\Models\SurveyAnswer[]|\Illuminate\Database\Eloquent\Collection $answers
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $user_id
 * @property string $answer
 * @property \App\Models\SurveyQuestion|null $question
 * @method static \Database\Factories\SurveyAnswerFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereFormularId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereSurveyQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyAnswer whereUserId($value)
 * @mixin \Eloquent
 * @deprecated
 */
final class SurveyAnswer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'survey_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'formular_id',
        'survey_question_id',
        'challenge_id',
        'answer'
    ];

    /**
     * relation get User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * relation get Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function question()
    {
        return $this->hasOne(SurveyQuestion::class, 'id', 'survey_question_id');
    }
}
