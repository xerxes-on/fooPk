<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\SalesPage;

use App\Services\Questionnaire\Question\BaseQuestionService;

/**
 * Service responsible for handling sales page related to Foodpunk team.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class TeamDetailsInfoPageService extends BaseQuestionService
{
    public function getVariations(): array
    {
        // todo: link should be dynamic
        return [
            'info' => trans(
                "questionnaire.questions.{$this->questionModel->slug}.options.info",
                locale: $this->locale
            ),
            'extra' => [
                'instagram' => 'https://instagram.com/foodpunk',
                'facebook'  => 'https://facebook.com/Foodpunk',
            ],
        ];
    }
}
