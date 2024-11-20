<?php

namespace App\Models;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * \App\Models\Admin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property string $lang
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $avatar
 * @property-read string $avatar_url_or_blank
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $liableClients
 * @property-read int|null $liable_clients_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Admin extends Authenticatable implements HasLocalePreference
{
    use Notifiable;
    use HasRoles;

    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'lang'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int,string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'avatar' => 'image',
    ];

    /**
     * Get the avatar url or blank.
     */
    public function getAvatarUrlOrBlankAttribute(): string
    {
        return $this?->avatar_url ?? asset('images/blank.png');
    }

    /**
     * Get an attribute from the model.
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // need for edit user
        if ($key == 'role') {
            $role = $this?->roles?->pluck('id')->toArray();
            return $role[0] ?? null;
        }

        if ($key == 'role_name') {
            $role = $this?->roles?->pluck('name')->toArray();
            return $role[0] ?? null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): string
    {
        return $this->lang;
    }

    public function liableClients(): MorphToMany
    {
        return $this->morphToMany(
            User::class,
            'model',
            'consultants_responsibilities',
            'admin_id',
            'client_id',
        );
    }

    public function isResponsibleForClient(int $clientID): bool
    {
        return $this->liableClients()->pluck('client_id')->contains($clientID);
    }
}
