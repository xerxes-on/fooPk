<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\SalesPage;

use App\Services\Questionnaire\Question\BaseQuestionService;

/**
 * Service responsible for handling testimonials info page.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class TestimonialInfoPageService extends BaseQuestionService
{
    public function getVariations(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }
}
