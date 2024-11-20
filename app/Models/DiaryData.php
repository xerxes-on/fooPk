<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DiaryData
 *
 * TODO: Image is deleted. Remove stapler bindings.
 *
 * @property int $id
 * @property int $user_id
 * @property float|null $weight
 * @property float|null $waist
 * @property float|null $upper_arm
 * @property float|null $leg
 * @property int|null $mood
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereLeg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereMood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereUpperArm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereWaist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiaryData whereWeight($value)
 */
final class DiaryData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'diary_datas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'weight',
        'waist',
        'upper_arm',
        'leg',
        'mood',
        'created_at' // do not remove this. Used in DiaryService::processStore
    ];

    /**
     * relation User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
