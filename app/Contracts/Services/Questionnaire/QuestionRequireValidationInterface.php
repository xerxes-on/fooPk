<?php

declare(strict_types=1);

namespace App\Contracts\Services\Questionnaire;

/**
 * Interface of a question that requires validation.
 *
 * @package App\Contracts\Services\Questionnaire
 */
interface QuestionRequireValidationInterface
{
    /**
     * Validate question answer over api.
     */
    public function validateOverApi(string $answer): bool;

    /**
     * Validate question answer over web app.
     */
    public function validateOverWeb(string|array $answer): bool;

    /**
     * Get validation error message.
     */
    public function getValidationErrorMessage(): string;
}
