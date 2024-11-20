<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Client\ClientCalculationActionsEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for handling client nutrition calculations.
 *
 * @property string $client_id
 * @property string $Kcal
 * @property string $KH
 * @property string $ew_percents
 * @property array $ingestion
 * @property string $action
 *
 * @package App\Http\Requests\Admin\Recipe
 */
final class ClientCalculationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo('create_client');
    }

    /**
     * Get the validation rules that apply to the request.
     * @note in $_POST there are some other values, but they are not used during processing in ClientNutrientsCalculationService
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id'   => ['integer', 'required', 'exists:' . User::class . ',id'],
            'Kcal'        => ['numeric'],
            'KH'          => ['numeric'],
            'ew_percents' => ['numeric'],

            'ingestion'            => ['array', 'min:1'],
            'ingestion.*.percents' => ['numeric'],
            'ingestion.*.Kcal'     => ['numeric'],
            'ingestion.*.KH'       => ['numeric'],
            'ingestion.*.EW'       => ['numeric'],
            'ingestion.*.F'        => ['numeric'],
            'action'               => ['required', 'string', 'in:' . implode(',', ClientCalculationActionsEnum::names())],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param array|int|string|null $key
     * @param mixed $default
     */
    public function validated($key = null, $default = null): array
    {
        $data                = parent::validated($key, $default);
        $data['client_id']   = (int)$data['client_id'];
        $data['Kcal']        = convertToNumber($data['Kcal']);
        $data['KH']          = convertToNumber($data['KH']);
        $data['ew_percents'] = convertToNumber($data['ew_percents']);

        // Convert ingestion values to numbers
        foreach ($data['ingestion'] as $newKey => $value) {
            $data['ingestion'][$newKey]['percents'] = convertToNumber($value['percents']);
            $data['ingestion'][$newKey]['Kcal']     = convertToNumber($value['Kcal']);
            $data['ingestion'][$newKey]['KH']       = convertToNumber($value['KH']);
            $data['ingestion'][$newKey]['EW']       = convertToNumber($value['EW']);
            $data['ingestion'][$newKey]['F']        = convertToNumber($value['F']);
        }

        $data['user'] = User::find($this->client_id);
        return (array)$data;
    }
}
