<?php

namespace Modules\Internal\Models;

use Illuminate\Database\Eloquent\{Builder, Model, Prunable};
use Modules\Internal\Enums\JobProcessingEnum;

/**
 * AdminStorage model representation.
 *
 * TODO: Typehint required, @NickMost
 *
 * @property int $id
 * @property string $key
 * @property array $data
 * @property string $created_at
 * @method static Builder|AdminStorage newModelQuery()
 * @method static Builder|AdminStorage newQuery()
 * @method static Builder|AdminStorage query()
 * @method static Builder|AdminStorage whereCreatedAt($value)
 * @method static Builder|AdminStorage whereData($value)
 * @method static Builder|AdminStorage whereId($value)
 * @method static Builder|AdminStorage whereKey($value)
 * @mixin \Eloquent
 */
final class AdminStorage extends Model
{
    use Prunable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'internal_admin_storage';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    public $fillable = [
        'key',
        'data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Bootstrap the model and its traits.
     * Overridden to set correct created_at field.
     */
    public static function boot(): void
    {
        parent::boot();
        self::creating(
            static function (AdminStorage $model) {
                $model->created_at = $model->freshTimestamp();
            }
        );
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return self::where('created_at', '<=', now()->subWeek());
    }

    /**
     * @param $userId
     * @return int
     */
    public static function getAfterFormularChangeJobHash($type, $userId)
    {
        $row = self::where('key', $type . $userId)->first()?->data;
        if (!empty($row['related_job'])) {
            return $row['related_job'];
        }
        return 0;
    }

    /**
     * @param $type
     * @param $userId
     * @param null $jobId
     * @return int
     */
    public static function generateAndSaveJobHash($type, $userId, $jobId = null)
    {
        if (is_null($jobId)) {
            $jobId = (int)(microtime(true) * 1000) + mt_rand();
        }
        self::updateOrCreate(
            ['key' => $type . $userId],
            [

                'data' => [
                    'start_date' => time(),
                    'data'       => [
                        'user_id' => $userId,
                    ],
                    'related_job' => $jobId
                ]
            ]
        );
        return $jobId;
    }

    /**
     * @param $userId
     * @param null $jobId
     * @return int
     */
    public static function generateAfterFormularChangeJobHash($userId, $jobId = null)
    {
        return self::generateAndSaveJobHash(JobProcessingEnum::AFTER_QUESTIONNAIRE_CHANGE->value, $userId, $jobId);
    }

    /**
     * @param $userId
     * @param null $jobId
     * @return int
     */
    public static function generatePreliminaryJobHash($userId, $jobId = null)
    {
        return self::generateAndSaveJobHash(JobProcessingEnum::PRELIMINARY_JOB->value, $userId, $jobId);
    }

    /**
     * @param $userId
     * @param null $jobId
     * @return int
     */
    public static function generateSyncUserExcludedIngredientsJobHash($userId, $jobId = null)
    {
        return self::generateAndSaveJobHash(JobProcessingEnum::USER_PROHIBITED_INGREDIENTS->value, $userId, $jobId);
    }
}
