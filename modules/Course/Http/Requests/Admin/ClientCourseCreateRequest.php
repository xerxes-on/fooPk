<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests\Admin;

use Carbon\Carbon;
use Modules\Course\Models\Course;

/**
 * Client Course create Request.
 *
 * @property int $user_id
 * @property int $course_id
 * @property string|Carbon $start_at
 * @property-read Carbon $ends_at
 * @property-read Course $course
 * @package Modules\Course\Http\Requests\Admin
 */
final class ClientCourseCreateRequest extends ClientCourseBaseRequest
{
    public function rules(): array
    {
        return [
            'user_id'   => ['required', 'integer'],
            'course_id' => ['required', 'integer'],
            'start_at'  => ['required', 'string', 'date_format:d.m.Y'],
        ];
    }

}
