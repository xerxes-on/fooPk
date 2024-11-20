<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests\Admin;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Course\Models\Course;

/**
 * Client Course Base Request.
 *
 * @package Modules\Course\Http\Requests\Admin
 */
abstract class ClientCourseBaseRequest extends FormRequest
{
    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $course = Course::findOrFail($this->course_id, ['id', 'duration']);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('course_id', 'Course not found');
                    return;
                }

                try {
                    $startAt = Carbon::parse($this->start_at)->startOfDay();
                    $endsAt  = $startAt->copy()->addDays($course->duration)->startOfDay();
                } catch (InvalidFormatException) {
                    $validator->errors()->add('start_at', 'Incorrect date format');
                    return;
                }

                $this->merge([
                    'course'   => $course,
                    'start_at' => $startAt,
                    'ends_at'  => $endsAt,
                ]);
            }];
    }

    public function authorize(): bool
    {
        return true;
    }
}
