<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests;

use App\Http\Requests\BaseRequest;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Validation\Validator;

/**
 * Course reschedule request
 *
 * @property integer $courseId
 * @property Carbon $startDate
 *
 * @package App\Http\Requests
 */
class CourseRescheduleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courseId'  => ['required', 'integer'],
            'startDate' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                try {
                    $date = Carbon::parse($this->startDate)->startOfDay();
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('startDate', $e->getMessage());
                    return;
                }

                $today = today()->startOfDay();
                if ($date->lt($today)) {
                    $validator->errors()->add('startDate', trans('course::common.schedule_in_past', ['date' => $today->toDateString()]));
                    return;
                }

                $this->merge([
                    'courseId'  => (int)$this->courseId,
                    'startDate' => $date,
                ]);
            }];
    }
}
