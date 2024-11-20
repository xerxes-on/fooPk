<?php
/**
 * @copyright   Copyright © 2019 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        14.05.2020
 */

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class allergy_types
 *
 * @package App\Models
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Allergy[]|Collection $allergies
 * @property int $id
 * @property-read int|null $allergies_count
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTypes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class AllergyTypes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'allergy_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class, 'type_id', 'id');
    }
}
