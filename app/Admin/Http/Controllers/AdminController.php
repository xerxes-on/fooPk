<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\AdminFormRequest;
use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Admin user controller.
 *
 * @package App\Http\Controllers\Admin
 */
final class AdminController extends Controller
{
    public function store(AdminFormRequest $request): RedirectResponse
    {
        $adminData = [
            'name'   => $request->name,
            'email'  => $request->email,
            'status' => $request->status ? UserStatusEnum::ACTIVE->value : UserStatusEnum::INACTIVE->value
        ];

        if (!empty($request->new_password)) {
            $adminData['password'] = Hash::make($request->new_password);
        }

        try {
            DB::beginTransaction();
            $admin = Admin::updateOrCreate(['id' => $request->id,], $adminData);
            $admin->syncRoles($request->role);
            $admin->liableClients()->sync($request->liableClients);
            DB::commit();
            $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
            $type    = 'success_message';
            $message = trans('common.' . $message);
        } catch (\Throwable $e) {
            logError($e);
            DB::rollBack();
            $message = $e->getMessage();
            $type    = 'error_message';
        }

        return redirect()->back()->with($type, $message);
    }
}
