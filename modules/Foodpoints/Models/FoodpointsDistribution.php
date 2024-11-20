<?php

namespace Modules\Foodpoints\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FoodpointsDistribution Model
 */
final class FoodpointsDistribution extends Model
{

    public $timestamps = false;

    protected $table = 'foodpoints_distribution';


    protected $fillable = [
        'amount',
        'type',
        'user_id'
    ];

}
