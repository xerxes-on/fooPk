<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request allowing to validate date periods.
 *
 * @used in PurchaseList
 *
 * @property string $date_start
 * @property string $date_end
 *
 * @package App\Http\Requests\ShoppingList
 */
final class DatePeriodRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date_start' => ['required', 'date', 'date_format:d.m.Y', 'before_or_equal:date_end'],
            'date_end'   => ['required', 'date', 'date_format:d.m.Y', 'after_or_equal:date_start'],
        ];
    }
}
