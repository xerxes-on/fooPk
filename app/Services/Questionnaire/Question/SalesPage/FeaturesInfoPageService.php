<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question\SalesPage;

use App\Services\Questionnaire\Question\BaseQuestionService;

/**
 * Service responsible for handling info page related to application features.
 *
 * @note This is a dummy service, as this page would be developed and devoted only to mobile applications.
 *
 * @package App\Services\Questionnaire\Question\InfoPage
 */
final class FeaturesInfoPageService extends BaseQuestionService
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
