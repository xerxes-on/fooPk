<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rule that allow to detect if the provided key exists in a single value in array.
 *
 * @package App\Rules
 */
final readonly class UniqueKeyPerArrayRule implements ValidationRule
{
    public function __construct(private string $uniqueKey)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail("The $attribute must be an array");
        }
        if ($this->hasMoreThenOneUniqueKeys($this->getKeys($value))) {
            $fail("The $this->uniqueKey must be unique per array");
        }
    }

    private function getKeys(array $value): array
    {
        $keys = [];
        foreach ($value as $item) {
            $keys[] = array_keys($item);
        }
        return \Arr::flatten($keys);
    }

    private function hasMoreThenOneUniqueKeys(array $keys): bool
    {
        return count(array_keys($keys, $this->uniqueKey)) > 1;
    }
}
