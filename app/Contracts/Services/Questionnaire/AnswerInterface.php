<?php

declare(strict_types=1);

namespace App\Contracts\Services\Questionnaire;

/**
 * Questionnaire answer interface.
 *
 * @package App\Contracts\Services\Questionnaire
 */
interface AnswerInterface
{
    /**
     * Retrieve answer and format it for output.
     */
    public function getFormattedAnswer(): string;

    /**
     * Reformat answer for saving derived from api.
     */
    public function reformatAnswerFromApi(string $answer): string;

    /**
     * Reformat answer for saving derived from web app.
     */
    public function reformatAnswerFromWeb(null|string|array $answer): string|array;

    /**
     * Get answer if it exists.
     */
    public function getAnswer(): string|array|null;

    /**
     * Try to obtain answer by identifier.
     * String identifier - usually a fingerprint for temporarily questionnaire.
     * Integer identifier - usually a users ID.
     */
    public function tryToGetAnswerByIdentity(string|int|null $identity = null): string|array|null;

    /**
     * Retrieve answer when user is editing questionnaire over api.
     */
    public function getAnswerForApiEditing(int $questionId, int $userId): string|array|null;
}
