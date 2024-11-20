<?php

declare(strict_types=1);

namespace Modules\Course\Http\Controllers\Admin;

use App\Events\AdminActionsTaken;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Course\Http\Requests\Admin\ClientCourseCreateRequest;
use Modules\Course\Http\Requests\Admin\ClientCourseEditRequest;
use Modules\Course\Http\Requests\Admin\UserCourseDestroyRequest;

class ClientCourseAdminController extends Controller
{
    public function store(ClientCourseCreateRequest $request): RedirectResponse
    {
        User::findOrFail($request->user_id)
            ->courses()
            ->attach(
                $request->course,
                [
                    'start_at' => $request->start_at,
                    'ends_at'  => $request->ends_at
                ]
            );

        AdminActionsTaken::dispatch(); //TODO: replace with users` changes cache actions

        return redirect()->back()->with('success_message', trans('admin.clients.challenges.messages.success'));
    }

    public function edit(ClientCourseEditRequest $request): JsonResponse
    {
        \DB::table('course_users')
            ->where('id', $request->user_course_id)
            ->update(
                [
                    'course_id' => $request->course->id,
                    'start_at'  => $request->start_at,
                    'ends_at'   => $request->ends_at
                ]
            );

        return response()->json(['success' => true]);
    }

    public function destroy(UserCourseDestroyRequest $request): JsonResponse
    {
        \DB::table('course_users')
            ->where('id', $request->user_course_id)
            ->delete();
        AdminActionsTaken::dispatch(); //TODO: replace with users` changes cache actions
        return response()->json(['success' => true]);
    }
}
