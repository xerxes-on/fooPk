<?php

declare(strict_types=1);

namespace App\Contracts\Services\Questionnaire;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface of a question which depends on another question.
 *
 * @package App\Contracts\Services\Questionnaire
 */
interface QuestionDependencyInterface
{
    /**
     * Get id of a question this question depends on.
     */
    public function getDependentQuestionId(): int;

    /**
     * Build dependency for question.
     * TODO: Probably we do not need models here as new way of settings data was introduced. Maybe leave only identifier?
     */
    public function buildDependency(Model $model, int|string $identifier): void;
}
