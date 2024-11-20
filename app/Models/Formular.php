<?php

namespace App\Models;

use App\Enums\FormularCreationMethodsEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Users formular.
 *
 * @package App\Models
 * @property-read int $id
 * @property-read \App\Models\User $user
 * @property bool $approved
 * @property bool $forced_visibility
 * @property \App\Models\Admin $creator
 * @property-read \App\Models\SurveyAnswer[]|\Illuminate\Database\Eloquent\Collection $answers
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $user_id
 * @property int|null $creator_id
 * @property FormularCreationMethodsEnum $creation_method Describe how formular was created, for free or paid by customer
 * @property-read int|null $answers_count
 * @method static \Database\Factories\FormularFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formular newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formular query()
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereForcedVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formular whereCreationMethod($value)
 * @mixin \Eloquent
 * @deprecated
 */
final class Formular extends Model
{
    use HasFactory;

    /**
     * Survey question ID, question with Diet
     */
    public const FORMULAR_SURVEY_QUESTION_ID_DIET = 14;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'formulars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'approved',
        'creator_id',
        'forced_visibility',
        'creation_method'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dietdata'          => 'array',
        'approved'          => 'boolean',
        'forced_visibility' => 'boolean',
        'creation_method'   => FormularCreationMethodsEnum::class,
    ];

    /**
     * relation Answers
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class)->orderBy('survey_question_id');
    }

    public function forceVisibility(): void
    {
        $this->forced_visibility = true;
        $this->save();
        if ($this->user) {
            $this->user->forgetFormularCache();
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * relation Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'creator_id');
    }

    /**
     * get Creator
     *
     * @return mixed|null
     */
    public function getCreator()
    {
        return $this->creator_id ? Admin::find($this->creator_id) : null;
    }

    public function getCreationMethodNameAttribute(): string
    {
        return ucfirst(strtolower($this->creation_method->name));
    }
}
