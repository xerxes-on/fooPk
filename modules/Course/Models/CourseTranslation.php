<?php

declare(strict_types=1);

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Course Translation Model.
 *
 * @property int $id
 * @property int|null $course_id
 * @property string|null $locale
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CourseTranslation extends Model
{
    protected $table    = 'course_translations';
    protected $fillable = ['title', 'description'];
}
