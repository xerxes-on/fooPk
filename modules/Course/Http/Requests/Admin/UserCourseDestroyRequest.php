<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests\Admin;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Destroy User Course Request.
 *
 * @property int $user_course_id
 * @package Modules\Course\Http\Requests\Admin
 */
final class UserCourseDestroyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_course_id' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo(PermissionEnum::DELETE_CLIENT_CHALLENGES->value);
    }
}
