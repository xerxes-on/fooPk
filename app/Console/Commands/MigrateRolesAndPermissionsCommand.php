<?php

namespace App\Console\Commands;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\Admin\Permission\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class MigrateRolesAndPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-roles-and-permissions-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update roles and permissions from the enum to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roles       = array_map('strtolower', array_column(RoleEnum::cases(), 'value'));
        $permissions = array_map('strtolower', array_column(PermissionEnum::cases(), 'value'));
        Role::get('name')
            ->each(function (Role $role) use (&$roles) {
                if (in_array($role->name, $roles) && ($key = array_search($role->name, $roles)) !== false) {
                    unset($roles[$key]);
                }
            });
        Permission::get('name')
            ->each(function (Permission $permission) use (&$permissions) {
                if (in_array($permission->name, $permissions) &&
                    ($key = array_search($permission->name, $permissions)) !== false) {
                    unset($permissions[$key]);
                }
            });

        if ($roles !== []) {
            foreach ($roles as $role) {
                $role = RoleEnum::from($role);
                Role::create([
                    'name'       => $role->value,
                    'guard_name' => $role->getGuardName(),
                ]);
            }
        }

        if ($permissions !== []) {
            foreach ($permissions as $permission) {
                Permission::create([
                    'name'       => $permission,
                    'guard_name' => RoleEnum::ADMIN_GUARD, // All permissions belong to admin guard by business requirements
                ]);
            }
        }

        truncate_tables(['role_has_permissions']);
        $permissions     = Permission::get(['id', 'name']);
        $rolePermissions = Role::get(['id', 'name'])
            ->map(fn(Role $item) => [
                'role_id'       => $item->id,
                'permission_id' => $permissions
                    ->whereIn('name', RoleEnum::from($item->name)->getPermission())
                    ->pluck('id')
                    ->toArray()
            ])
            ->filter(fn(array $item) => !empty($item['permission_id']))->toArray();

        foreach ($rolePermissions as $role) {
            foreach ($role['permission_id'] as $permission) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission,
                    'role_id'       => $role['role_id']
                ]);
            }
        }

        $this->info('Roles and permissions have been updated successfully. Clearing cache...');
        $this->call('optimize:clear');
        $this->info('Cache cleared successfully.');

        return self::SUCCESS;
    }
}
