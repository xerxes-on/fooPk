<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests;

use App\Http\Requests\BaseRequest;

/**
 * Buy course request
 *
 * @property integer|string $challengeId
 * @property integer|null $foodPoint
 * @property string $startDate
 *
 * @package App\Http\Requests
 */
class BuyCourseRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challengeId' => ['required', 'integer'],
            'startDate'   => ['required', 'date_format:d.m.Y'],
        ];
    }
}
