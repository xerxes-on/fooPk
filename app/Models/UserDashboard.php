<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserDashboard
 *
 * @package App\Models
 * @property int $id
 * @property string|null $message
 * @property int|null $wp_article_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDashboard whereWpArticleId($value)
 * @mixin \Eloquent
 */
class UserDashboard extends Model
{
    protected $table = 'user_dashboard';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message', 'wp_article_id'];
}
