<?php

declare(strict_types=1);

namespace App\Contracts\Services\Questionnaire;

use App\Http\Resources\Questionnaire\QuestionnaireQuestionResource;
use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Questionnaire question interface.
 *
 * @package App\Contracts\Services\Questionnaire
 */
interface QuestionInterface
{
    /**
     * Get question variations (potential options).
     */
    public function getVariations(): array;

    /**
     * Get question title.
     */
    public function getTitle(): string;

    /**
     * Get question subtitle.
     */
    public function getSubtitle(): ?string;

    /**
     * Get question Json resource.
     */
    public function getResource(): QuestionnaireQuestionResource;

    /**
     * Get potential next question.
     *
     * @throws ModelNotFoundException
     */
    public function defineNextQuestion(Model $model, int|string $identifier): QuestionnaireQuestion;

    /**
     * Get potential previous question.
     *
     * @throws ModelNotFoundException
     */
    public function definePreviousQuestion(Model $model, int|string $identifier): QuestionnaireQuestion;

    /**
     * Define if question has dependency.
     */
    public function hasDependency(): bool;

    /**
     * Define if question should be validated somehow.
     */
    public function mustBeValidated(): bool;

    /**
     * Get special rules for question that can exclude another questions.
     */
    public function getExclusionRules(): array;
}
