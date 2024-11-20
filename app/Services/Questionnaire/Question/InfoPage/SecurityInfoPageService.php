<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\InfoPage;

use App\Services\Questionnaire\Question\BaseQuestionService;

/**
 * Service responsible for handling security info page.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class SecurityInfoPageService extends BaseQuestionService
{
    public function getVariations(): array
    {
        return [
            'info' => trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.info",
                locale: $this->locale
            ),
            'extra' => trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.extra",
                locale: $this->locale
            )
        ];
    }
}
