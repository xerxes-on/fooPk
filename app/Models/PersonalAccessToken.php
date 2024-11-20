<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, Prunable};
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * PersonalAccessToken handled by Laravel Sanctum.
 *
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array|null $abilities
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $tokenable
 * @method static Builder|PersonalAccessToken newModelQuery()
 * @method static Builder|PersonalAccessToken newQuery()
 * @method static Builder|PersonalAccessToken query()
 * @method static Builder|PersonalAccessToken whereAbilities($value)
 * @method static Builder|PersonalAccessToken whereCreatedAt($value)
 * @method static Builder|PersonalAccessToken whereExpiresAt($value)
 * @method static Builder|PersonalAccessToken whereId($value)
 * @method static Builder|PersonalAccessToken whereLastUsedAt($value)
 * @method static Builder|PersonalAccessToken whereName($value)
 * @method static Builder|PersonalAccessToken whereToken($value)
 * @method static Builder|PersonalAccessToken whereTokenableId($value)
 * @method static Builder|PersonalAccessToken whereTokenableType($value)
 * @method static Builder|PersonalAccessToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use Prunable;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        $now = now();
        return self::where('last_used_at', '<=', $now->subMonth())
            ->orWhereNull('last_used_at')
            ->orWhere('expires_at', '<=', $now->subHours(24)); // in case of external integrations
    }
}
