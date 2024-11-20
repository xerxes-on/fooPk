<?php

namespace App\Admin\Http\Requests\Client;

use App\Enums\Admin\Permission\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClientFormRequest
 *
 * @property int $userId
 * @property int|string $approve
 *
 * @package App\Http\Requests
 */
final class ClientFormApproveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)$this->user()?->hasPermissionTo(PermissionEnum::CREATE_CLIENT->value);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'userId'  => ['required', 'exists:users,id'],
            'approve' => ['required', 'string', 'in:true,false'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge(
            [
                'approve' => $this->approve === 'true' ? 1 : 0,
            ]
        );
    }
}
