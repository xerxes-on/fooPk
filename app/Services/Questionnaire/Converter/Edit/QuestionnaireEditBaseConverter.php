<?php

namespace App\Services\Questionnaire\Converter\Edit;

use App\Enums\FormularCreationMethodsEnum;
use App\Http\Traits\Questionnaire\CanHandleQuestionnaireCreation;
use App\Http\Traits\Questionnaire\CanHandleQuestionnaireEditing;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;

// TODO:: @NickMost refactor that when flow will be ok from mobile app

/**
 * Base converter for questionnaire editing purposes.
 *
 * @package App\Services\Questionnaire\Converter\Edit
 */
abstract class QuestionnaireEditBaseConverter
{
    use CanHandleQuestionnaireEditing;

    // TODO:: @NickMost refactor that when flow will be ok from mobile app
    use CanHandleQuestionnaireCreation;

    /**
     * TODO: pay attention to this. maybe some extra checks needed?
     * TODO: not all cases are covered
     * @note too complicated, to pass any extra data. So instead,
     * we check if user has paid for questionnaire in last 10 minutes
     */
    protected function setQuestionnaireCreationMethod(User $user): FormularCreationMethodsEnum
    {
        $creationMethod = FormularCreationMethodsEnum::FREE;
        if ($user->transactions()
            ->whereBetween('created_at', [now()->subMinutes(10), now()])
            ->where([
                ['type', Transaction::TYPE_WITHDRAW],
                ['amount', (int)config('formular.formular_editing_price_foodpoints') * -1]
            ])
            ->exists()
        ) {
            $creationMethod = FormularCreationMethodsEnum::PAID;
        }
        return $creationMethod;
    }
}
