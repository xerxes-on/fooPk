<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Temporary Questionnaire model
 *
 * @property int $id
 * @property int $questionnaire_question_id
 * @property string $lang
 * @property string $fingerprint Device unique identifier/fingerprint to identify unregistered user
 * @property array $answer
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\QuestionnaireQuestion|null $question
 * @method static Builder|QuestionnaireTemporary newModelQuery()
 * @method static Builder|QuestionnaireTemporary newQuery()
 * @method static Builder|QuestionnaireTemporary query()
 * @method static Builder|QuestionnaireTemporary whereAnswer($value)
 * @method static Builder|QuestionnaireTemporary whereCreatedAt($value)
 * @method static Builder|QuestionnaireTemporary whereFingerprint($value)
 * @method static Builder|QuestionnaireTemporary whereId($value)
 * @method static Builder|QuestionnaireTemporary whereLang($value)
 * @method static Builder|QuestionnaireTemporary whereQuestionnaireQuestionId($value)
 * @method static Builder|QuestionnaireTemporary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class QuestionnaireTemporary extends Model
{
    use Prunable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temporary_questionnaires';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'questionnaire_question_id',
        'lang',
        'fingerprint',
        'answer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'answer' => Json::class,
    ];

    /**
     * Relation to question.
     */
    public function question(): HasOne
    {
        return $this->hasOne(QuestionnaireQuestion::class, 'id', 'questionnaire_question_id')->orderBy('order');
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return self::where('updated_at', '<=', now()->subDay());
    }

    protected function pruning(): void
    {
        self::where('fingerprint', $this->fingerprint)->delete();
    }
}
