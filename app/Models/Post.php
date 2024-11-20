<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neko\Stapler\ORM\{EloquentTrait, StaplerableInterface};

/**
 * User diary Post model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string|null $image_file_name
 * @property int|null $image_file_size
 * @property string|null $image_content_type
 * @property string|null $image_updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Neko\Stapler\Attachment $image
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereImageContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereImageFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereImageFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereImageUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUserId($value)
 * @mixin \Eloquent
 */
final class Post extends Model implements StaplerableInterface
{
    use EloquentTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'content',
        'image',
        'diary_data_id'
    ];

    /**
     * DiaryData constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'image',
            [
                'styles' => [
                    'medium' => '600x600#',
                    'thumb'  => '200x200#'
                ],
                'url'         => '/diary/:id/:style/:filename',
                'path'        => ':app_root/storage/app/:url',
                'default_url' => config('stapler.api_url') . '/160/00a65a/ffffff/?text=P'
            ]
        );

        parent::__construct($attributes);
    }

    /**
     * Get all current attributes on the model.
     *
     * Allows to correctly obtain attributes for STAPLER package as it collects
     *
     * @see https://github.com/CodeSleeve/laravel-stapler/issues/64#issuecomment-338445440
     * @note Must not be removed.
     * @return array
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }

    /**
     * Relation for User model.
     *
     * @return BelongsTo<User, Post>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
