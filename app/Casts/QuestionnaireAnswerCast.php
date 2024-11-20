<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

/**
 * Json or string attribute caster for questionnaire answer model.
 *
 * @package App\Casts
 */
final class QuestionnaireAnswerCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string|array
    {
        $returnData = [];
        try {
            $returnData = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $returnData = $value;
        }

        return is_array($returnData) ? $returnData : (string)$value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (is_array($value)) {
            $encodedAnswer = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return $encodedAnswer === false ? '' : $encodedAnswer;
        }
        return (string)$value;
    }
}
