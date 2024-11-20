<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\PushNotification\Http\Requests\Admin\NotificationTypeRequest;
use Modules\PushNotification\Models\NotificationType;

/**
 * Controller for NotificationType.
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationTypeAdminController extends Controller
{
    public function store(NotificationTypeRequest $request): RedirectResponse
    {
        NotificationType::updateOrCreate(['id' => $request->id], $request->validated());

        $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
        return redirect()
            ->back()
            ->with('success_message', trans('common.' . $message));
    }
}
