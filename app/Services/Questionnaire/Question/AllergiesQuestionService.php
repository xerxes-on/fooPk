<?php

declare(strict_types=1);

namespace App\Services\Questionnaire\Question;

use App\Http\Traits\Questionnaire\PrepareDiseasesForValidation;
use App\Models\Allergy;
use Illuminate\Database\Query\Builder;

/**
 * Service responsible for handling client allergies question.
 *
 * @package App\Services\Questionnaire\Question
 */
final class AllergiesQuestionService extends BaseValidationRequireQuestionService
{
    use PrepareDiseasesForValidation;

    public const ANSWER_HIST   = 'hist';
    public const ANSWER_OXALIC = 'oxalic';

    public function getVariations(): array
    {
        $options = Allergy::whereTypeId(
            static fn(Builder $q) => $q->select('id')->from('allergy_types')->whereName('allergy')
        )
            ->get()
            ->pluck("name:$this->locale", 'slug')
            ->toArray();
        $options[self::OTHER_OPTION_SLUG] = trans('common.other', locale: $this->locale);
        return $options;
    }
}
