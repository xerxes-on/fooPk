<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Job
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property int $attempts
 * @property int $available_at
 * @property \Illuminate\Support\Carbon|null $reserved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereAvailableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereReservedAt($value)
 * @mixin \Eloquent
 */
final class Job extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'];
}
