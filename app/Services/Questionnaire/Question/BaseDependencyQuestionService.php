<?php

namespace App\Services\Questionnaire\Question;

use App\Contracts\Services\Questionnaire\QuestionDependencyInterface;

abstract class BaseDependencyQuestionService extends BaseQuestionService implements QuestionDependencyInterface
{
    protected int $dependOnQuestion = 0;

    protected ?array $dependAnswer = null;

    public function getDependentQuestionId(): int
    {
        return $this->dependOnQuestion;
    }
}
