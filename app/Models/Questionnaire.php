<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DatabaseTableEnum;
use App\Enums\FormularCreationMethodsEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Questionnaire Model
 *
 * @property int $id
 * @property int $user_id
 * @property bool $is_approved Is it approved by admin
 * @property bool $is_editable Can it be edited by user
 * @property int|null $creator_id Null - created by user, otherwise - admin id
 * @property FormularCreationMethodsEnum $creation_method Describe how it was created. See FormularCreationMethodsEnum.php
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuestionnaireAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Admin|null $creator
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire query()
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereCreationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereIsEditable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Questionnaire whereUserId($value)
 * @mixin \Eloquent
 */
final class Questionnaire extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = DatabaseTableEnum::QUESTIONNAIRE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'is_approved',
        'is_editable',
        'creator_id',
        'creation_method',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_approved'     => 'boolean',
        'is_editable'     => 'boolean',
        'creation_method' => FormularCreationMethodsEnum::class,
    ];

    /**
     * Bootstrap the model and its traits.
     */
    public static function boot(): void
    {
        parent::boot();
        // Clean model relations
        self::deleting(function (Questionnaire $model) {
            $model->answers()->delete();
        });
    }

    /**
     * Relation to answers.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class)->orderBy('questionnaire_question_id');
    }

    /**
     * Relation to user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to answers creator (null mean user, otherwise Admin id).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'creator_id');
    }

    /**
     * Interact with the user's first name.
     */
//    public function getCreatorAttribute(): ?Admin
//    {
//        // todo: infinite loop detected when using $this->getAttribute('creator'). fix later
//        return $this->creator_id ? Admin::find($this->creator_id) : null;
//        // $this->getAttribute('creator') ?? null ;#? $this->getAttribute('creator') : null;
//    }
}
