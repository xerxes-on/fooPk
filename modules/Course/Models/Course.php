<?php

declare(strict_types=1);

namespace Modules\Course\Models;

use App\Models\TranslatableStaplerModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;
use Modules\Course\Enums\CourseStatus;
use Modules\Course\Enums\UserCourseStatus;
use Modules\Course\Service\CourseService;

/**
 * Course Model.
 *
 * @property int $id
 * @property int $duration
 * @property int|null $foodpoints
 * @property \Illuminate\Support\Carbon|null $minimum_start_at
 * @property CourseStatus $status
 * @property string|null $image_updated_at
 * @property string|null $image_content_type
 * @property int|null $image_file_size
 * @property string|null $image_file_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read EloquentCollection<int, \Modules\Course\Models\CourseArticle> $articles
 * @property-read int|null $articles_count
 * @property-read \Modules\Course\Models\CourseTranslation|null $translation
 * @property-read EloquentCollection<int, \Modules\Course\Models\CourseTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \Neko\Stapler\Attachment $image
 * @method static Builder|Course active()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel listsTranslations(string $translationField)
 * @method static Builder|Course newModelQuery()
 * @method static Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translated()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translatedIn(?string $locale = null)
 * @method static Builder|Course whereCreatedAt($value)
 * @method static Builder|Course whereDuration($value)
 * @method static Builder|Course whereFoodpoints($value)
 * @method static Builder|Course whereId($value)
 * @method static Builder|Course whereImageContentType($value)
 * @method static Builder|Course whereImageFileName($value)
 * @method static Builder|Course whereImageFileSize($value)
 * @method static Builder|Course whereImageUpdatedAt($value)
 * @method static Builder|Course whereMinimumStartAt($value)
 * @method static Builder|Course whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel withTranslation()
 * @mixin \Eloquent
 */
final class Course extends TranslatableStaplerModel
{
    public $table = 'courses';

    protected $fillable = [
        'duration',
        'foodpoints',
        'minimum_start_at',
        'status',
        'image_file_name'
    ];

    public $translatedAttributes = ['title', 'description'];

    protected $with = ['translations'];

    protected $casts = [
        'minimum_start_at' => 'date',
    ];

    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'image',
            [
                'styles' => [
                    'medium' => [
                        'dimensions'      => '600x600#',
                        'convert_options' => ['quality' => 90],
                    ],
                    'mobile' => [
                        'dimensions'      => '540x1080',
                        'convert_options' => ['quality' => 90],
                    ],
                    'thumb' => [
                        'dimensions'      => '400x400#',
                        'convert_options' => ['quality' => 90],
                    ],
                ],
                'url'         => '/uploads/challenges/:id/:style/:filename',
                'default_url' => config('stapler.api_url') . '/150/00a65a/ffffff/?text=C'
            ]
        );

        parent::__construct($attributes);
    }

    /**
     * @return HasMany<CourseArticle>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(CourseArticle::class, 'course_id', 'id')->orderBy('days');
    }

    public function getActiveDays(): int
    {
        return app(CourseService::class)->getActiveDays($this);
    }

    /**
     * @param Builder<Course> $query
     * @return Builder<Course>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::ACTIVE->value);
    }

    /**
     * @return EloquentCollection<int,Course>
     */
    public static function getAll(?User $user = null): EloquentCollection
    {
        return app(CourseService::class)->all($user);
    }

    /**
     * @return EloquentCollection<int,Course>
     */
    public static function getPurchasable(User $user): EloquentCollection
    {
        return app(CourseService::class)->purchasable($user);
    }

    /**
     * @return SupportCollection<int,Course>
     */
    public static function getSpecific(User $user, int $id): SupportCollection
    {
        return app(CourseService::class)->getSpecific($user, $id);
    }

    public static function getUserCourses(User $user): SupportCollection
    {
        return app(CourseService::class)->getUserCourses($user);
    }

    public function getCourseArticle(): array
    {
        return app(CourseService::class)->getCourseArticles($this);
    }

    public function getStatus(): UserCourseStatus
    {
        return app(CourseService::class)->getStatus($this);
    }

    public function getActualPrice(SupportCollection $userCourses): int
    {
        return app(CourseService::class)->getActualCoursePrice($this, $userCourses);
    }
}
