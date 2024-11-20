<?php

namespace Database\Seeders;

use App\Enums\Admin\Permission\{PermissionEnum, RoleEnum};
use App\Models\{Admin, Permission, Role, User};
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws \Exception
     */
    final public function run(): void
    {
        truncate_tables([
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'roles',
            'permissions'
        ]);

        foreach (RoleEnum::listRoles() as $item) {
            Role::create($item);
        }

        foreach (PermissionEnum::listPermissions() as $item) {
            Permission::create($item);
        }

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

        // First must be admin
        $adminId = Admin::whereId(1)->first('id')?->id;
        if (!is_null($adminId)) {
            DB::table('model_has_roles')->insert([
                'role_id'    => Role::whereName(RoleEnum::ADMIN->value)->first('id')->id,
                'model_type' => 'App\Models\Admin',
                'model_id'   => $adminId
            ]);
        }

        // We must set all user with corresponding role
        $userRoleId = Role::whereName(RoleEnum::USER->value)->first('id')?->id;
        if (is_null($userRoleId)) {
            throw new Exception('User role not found');
        }
        User::pluck('id')->chunk(1000)->each(function ($users) use ($userRoleId) {
            $users = $users
                ->map(fn($user) => [
                    'role_id'    => $userRoleId,
                    'model_type' => 'App\Models\User',
                    'model_id'   => $user
                ])
                ->toArray();
            DB::table('model_has_roles')->insert($users);
        });
    }
}
