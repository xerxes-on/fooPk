<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\CanAlwaysAuthorizeRequests;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

/**
 * Class DiaryFormRequest
 *
 * @property string|null $weight
 * @property string|null $waist
 * @property string|null $upper_arm
 * @property string|null $leg
 * @property string|null $mood
 * @property-read string|null $date
 * @property \Carbon\Carbon $created_at
 *
 * @package App\Http\Requests
 */
final class DiaryFormRequest extends FormRequest
{
    use CanAlwaysAuthorizeRequests;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'weight'    => ['nullable', 'regex:/^([0-9]*[,|.])?[0-9]+$/'],
            'waist'     => ['nullable', 'regex:/^([0-9]*[,|.])?[0-9]+$/'],
            'upper_arm' => ['nullable', 'regex:/^([0-9]*[,|.])?[0-9]+$/'],
            'leg'       => ['nullable', 'regex:/^([0-9]*[,|.])?[0-9]+$/'],
            'mood'      => ['nullable', 'numeric'],
            'date'      => ['nullable', 'string', 'date']
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                $converted = [];
                // Convert date into desired format
                try {
                    $converted['created_at'] = Carbon::parse($this->post('date'));
                } catch (InvalidFormatException $e) {
                    $validator->errors()->add('date', $e->getMessage());
                    return;
                }

                // Replace formats appeared in incoming data
                foreach ($this->only(['weight', 'waist', 'upper_arm', 'leg']) as $key => $value) {
                    $converted[$key] = str_replace(',', '.', (string)$value);
                }

                // Removing values that appear to be empty
                $converted = Arr::where(
                    $converted,
                    static function ($value) {
                        if (!empty($value)) {
                            return $value;
                        }
                    }
                );

                $this->merge($converted);
            }
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = [
            'weight'     => $this->weight,
            'waist'      => $this->waist,
            'upper_arm'  => $this->upper_arm,
            'leg'        => $this->leg,
            'mood'       => $this->mood,
            'created_at' => $this->created_at
        ];

        if (null === $key) {
            return $data;
        }

        return $data[$key] ?? $default;
    }
}
