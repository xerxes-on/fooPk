<?php

namespace App\Http\Requests\API\Meal;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Date filter.
 *
 * @property-read int $year
 * @property-read int $week
 * @property-read int $day
 * @property-read Carbon $date
 * @property-read string $filter
 *
 * @package App\Http\Requests\API\Meal
 */
final class MealWeekFilterRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'year' => ['integer'],
            'week' => ['integer', 'min:1', 'max:53'],
            'day'  => ['integer', 'min:0', 'max:6'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $date = new Carbon();

        $filter = 'weekly';
        // We need to ensure the date is existing correctly and is not related to next month
        if (isset($this->year) && isset($this->week) && isset($this->day)) {
            $date->setISODate($this->year, $this->week, $this->day);
            $filter = 'daily';
        } elseif (isset($this->year) && isset($this->week)) {
            $date->setISODate($this->year, $this->week);
        }

        $this->merge(
            [
                'date'   => $date,
                'filter' => $filter
            ]
        );
    }
}
