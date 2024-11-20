<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Question of a formular/questionnaire
 *
 * @property int $id
 * @property string $key_code
 * @property string $type
 * @property string|null $options
 * @property string|null $attributes
 * @property int $required
 * @property int $order
 * @property int $active
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @property-read \Carbon\Carbon $deleted_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereKeyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SurveyQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 * @deprecated
 */
final class SurveyQuestion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'survey_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'type',
        'options',
        'order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'options'    => 'array',
        'attributes' => 'array'
    ];
}
