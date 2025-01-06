<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\MealtimeEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rule that allow to detect a user tries to replace recipe for ingestion he is not allowed to.
 *
 * @package App\Rules
 */
final readonly class RecipeReplacementRule implements ValidationRule
{
    public function __construct(private User $user)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $ingestion = MealtimeEnum::tryFromValue($value);
        } catch (\InvalidArgumentException) {
            $fail(trans('validation.can', ['attribute' => $attribute]));
        }
        if ($this->user->allowed_ingestions->contains('id', $ingestion->value) === false) {
            $fail(trans('validation.attributes.ingestion.not_allowed'));
        }
    }
}
